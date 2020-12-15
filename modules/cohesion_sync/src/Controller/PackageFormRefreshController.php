<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PackageFormRefreshController.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class PackageFormRefreshController extends ControllerBase {

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * PackageFormRefreshController constructor.
   *
   * @param \Drupal\cohesion_sync\PackagerManager $packagerManager
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\cohesion\UsagePluginManager $usagePluginManager
   */
  public function __construct(PackagerManager $packagerManager, EntityRepository $entityRepository, EntityTypeManagerInterface $entityTypeManager, UsagePluginManager $usagePluginManager) {
    $this->packagerManager = $packagerManager;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->usagePluginManager = $usagePluginManager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('cohesion_sync.packager'), $container->get('entity.repository'), $container->get('entity_type.manager'), $container->get('plugin.manager.usage.processor'));
  }

  /**
   * Entrypoint. The form app asked to re-calculate the requirements and contents form.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function index(Request $request) {
    // Decode the settings sent from the app.
    $settings = json_decode($request->getContent(), TRUE);

    /**
     * Build the package requirements form.
     */
    $package_requirements_form = [];

    foreach ($this->getGroupsList() as $definition) {
      $entity_type_id = $definition['entity_type']->id();

      // Build the group data (sorted by label).
      $query = $definition['storage']->getQuery();
      if ($definition['entity_type']->hasKey('label')) {
        $query->sort($definition['entity_type']->getKey('label'), 'ASC');
      }

      $ids = $query->execute();

      $groups = [];

      foreach ($definition['storage']->loadMultiple($ids) as $entity) {
        $group_label = $this->getGroupLabelFromUsage($definition['usage'], $entity, $definition['entity_type']->getPluralLabel()->__toString());
        $group_id = str_replace(' ', '_', $group_label);

        // iI the entity is a content template, we want to filter by only modified ones.
        if ($entity_type_id != 'cohesion_content_templates' || $entity->isModified()) {
          // Add the entity to the group array.
          $groups[$group_id]['label'] = $group_label;

          $groups[$group_id]['items'][$entity->uuid()] = [
            'label' => $entity->label() ?? $entity->id(),
            'type' => $definition['entity_type']->id(),
          ];
        }
      }

      // Set the group data including the label.
      $package_requirements_form[$entity_type_id] = [
        'label' => ucfirst($definition['entity_type']->getPluralLabel()->__toString()),
        'groups' => $groups,
        'config_type' => $definition['usage']['config_type'],
      ];
    }

    /**
     * Build the package contents form.
     */
    $excluded_entity_type_ids = array_keys($settings['excludedSettings']);
    $package_contents_form = [];

    foreach ($settings['packageSettings'] as $uuid => $meta) {
      $source_entity_uuid = $uuid;
      $source_entity_type = $meta['type'];

      if ($source_entity = $this->entityRepository->loadEntityByUuid($source_entity_type, $source_entity_uuid)) {
        // Get details about the source entity type.
        $source_entity_type_id = $source_entity->getEntityTypeID();
        $source_entity_type_label = ucfirst($this->entityTypeManager->getDefinition($source_entity_type_id)->getPluralLabel()->__toString());

        // Set source entity type details in the form.
        $package_contents_form[$source_entity_type_id]['label'] = $source_entity_type_label;

        // Lop over the dependency groups.
        $dependency_groups = [];
        foreach ($this->packagerManager->buildPackageEntityList([$source_entity], $excluded_entity_type_ids) as $dependency) {
          // Get the label of the entity type.
          $entity_type_label = ucfirst($this->entityTypeManager->getDefinition($dependency['type'])->getPluralLabel()->__toString());

          $group_id = $dependency['type'];

          // Set the label of the dependency group (done repeatedly, which is a bit inefficient).
          $dependency_groups[$group_id]['label'] = $entity_type_label;

          // Set the uuid and type, etc. of the actual dependent entity.
          $dependency_groups[$group_id]['items'][$dependency['entity']->uuid()] = [
            'label' => $dependency['entity']->label() ?? $dependency['entity']->id(),
            'type' => $dependency['entity']->getEntityTypeID(),
          ];
        }
        ksort($dependency_groups);

        // Build the top level entry.
        $package_contents_form[$source_entity_type_id]['entities'][$source_entity->uuid()] = [
          'label' => $source_entity->label() ?? $source_entity->id(),
          'groups' => $dependency_groups,
        ];
      }
    }
    ksort($package_contents_form);

    /**
     * Build the excluded entity types list.
     */
    $excluded_entity_types_form = [];
    foreach ($this->usagePluginManager->getDefinitions() as $item) {
      if ($item['exportable']) {
        try {
          $excluded_entity_types_form[$item['entity_type']] = [
            'label' => $this->entityTypeManager->getDefinition($item['entity_type'])->getPluralLabel()->__toString(),
          ];
        }
        catch (\Throwable $e) {
          continue;
        }
      }
    }
    ksort($excluded_entity_types_form);

    /**
     * Return the forms to the app.
     */
    return new CohesionJsonResponse([
      'status' => 200,
      'packageRequirementsForm' => $package_requirements_form,
      'packageContentsForm' => $package_contents_form,
      'excludedEntityTypesForm' => $excluded_entity_types_form,
    ]);
  }

  /**
   * Given a Usage plugin definition and an entity, get the group name. Used
   * in the LHS panel form() to organize the entities.
   *
   * @param $usage
   * @param $entity
   * @param $all_suffix
   *
   * @return string
   */
  private function getGroupLabelFromUsage($usage, $entity, $all_suffix) {
    $group_key_entity_type = explode(',', $usage['group_key_entity_type']);

    // Get the group label.
    if ($usage['group_key']) {
      $label = [];

      foreach (explode(',', $usage['group_key']) as $index => $group_key) {

        // The group is the string of the group key value.
        if (!$usage['group_key_entity_type'][$index]) {
          // Probably a content template.
          if ($group_key) {
            $label[] = $entity->get($group_key);
          }
          else {
            $label[] = 'All';
          }
        }
        // If the group label is an entity reference get the label from the entity type definition.
        else {
          try {
            $label[] = 'All ' . $this->entityTypeManager->getStorage($group_key_entity_type[$index])->load($entity->get($group_key))->label();
          }
          catch (\Throwable $e) {
            return 'All';
          }
        }
      }

      return implode(' » ', $label);
    }
    else {
      return 'All';
    }
  }

  /**
   * Create a list of entity types with definitions for the LHS requirements panel.
   * See: form().
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function getGroupsList() {
    $list = [];

    $definitions = $this->usagePluginManager->getDefinitions();

    foreach ($definitions as $usage_plugin_definition) {
      // Only include entity types that are ste to appear in this list (as defined in their Usage plugin).
      if (!$usage_plugin_definition['exclude_from_package_requirements']) {
        // Get the entity type definition.
        try {
          $entity_type_definition = $this->entityTypeManager->getDefinition($usage_plugin_definition['entity_type']);
        }
        catch (\Throwable $e) {
          continue;
        }

        // Only include config entities.
        if ($entity_type_definition instanceof ConfigEntityType) {
          $list[$entity_type_definition->getPluralLabel()->__toString()] = [
            'usage' => $usage_plugin_definition,
            'entity_type' => $entity_type_definition,
            'storage' => $this->entityTypeManager->getStorage($usage_plugin_definition['entity_type']),
          ];

        }
      }
    }

    ksort($list);
    return $list;
  }

}
