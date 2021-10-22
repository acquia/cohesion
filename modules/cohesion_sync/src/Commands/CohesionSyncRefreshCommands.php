<?php

namespace Drupal\cohesion_sync\Commands;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drush\Commands\DrushCommands;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\CohesionSyncRefreshManager;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class CohesionSyncRefreshCommands extends DrushCommands {
  use DependencySerializationTrait;

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
   * @var \Drupal\cohesion_sync\CohesionSyncRefreshManager
   */
  protected $cohesionRefreshSyncManager;

  /**
   * PackageFormRefreshController constructor.
   *
   * @param \Drupal\cohesion_sync\PackagerManager $packagerManager
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\cohesion\UsagePluginManager $usagePluginManager
   * @param \Drupal\cohesion_sync\CohesionSyncRefreshManager $cohesionRefreshSyncManager
   */
  public function __construct(PackagerManager $packagerManager,
                              EntityRepository $entityRepository,
                              EntityTypeManagerInterface $entityTypeManager,
                              UsagePluginManager $usagePluginManager,
                              CohesionSyncRefreshManager $cohesionRefreshSyncManager) {
    $this->packagerManager = $packagerManager;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->usagePluginManager = $usagePluginManager;
    $this->cohesionRefreshSyncManager = $cohesionRefreshSyncManager;
  }

  /**
   * Export DX8 packages to sync.
   *
   * @validate-module-enabled cohesion_sync
   *
   * @param string $build_package_type
   *   The Build Package Type.
   *
   * @command sync:refresh
   * @aliases sync-refresh
   */
  public function refresh(string $build_package_type) {
    // Get Request Settings.
    $settings = $this->cohesionRefreshSyncManager->getRequestSettings();
    $excluded_entity_type_ids = array_keys($settings['excludedSettings']);

    switch ($build_package_type) {
      case CohesionSyncRefreshManager::PACKAGE_REQUIREMENTS:
        $requirement_batch = [
          'title' => t('Building Package Requirements Form'),
          'operations' => [],
          'finished' => [CohesionSyncRefreshCommands::class, 'packageBuildFinished']
        ];

        foreach($this->cohesionRefreshSyncManager->getGroupsList() as $definition) {
          $requirement_batch['operations'][] = [[CohesionSyncRefreshCommands::class, 'packageRequirements'], [$this->cohesionRefreshSyncManager, $definition]];
        }

        batch_set($requirement_batch);
        break;

      case CohesionSyncRefreshManager::PACKAGE_CONTENTS:
        $content_batch = [
          'title' => t('Building Package Contents Form'),
          'operations' => [],
          'finished' => [CohesionSyncRefreshCommands::class, 'packageBuildFinished']
        ];

        foreach ($settings['packageSettings'] as $uuid => $meta) {
          $content_batch['operations'][] = [[CohesionSyncRefreshCommands::class, 'packageBuild'], [$uuid, $meta, $excluded_entity_type_ids, $this]];
        }
        batch_set($content_batch);
        break;

      case CohesionSyncRefreshManager::PACKAGE_EXCLUDE_ENTITY_TYPES:
        $exclude_entities_batch = [
          'title' => t('Build the excluded entity types list'),
          'operations' => [],
          'finished' => [CohesionSyncRefreshCommands::class, 'packageBuildFinished']
        ];

        foreach ($this->usagePluginManager->getDefinitions() as $item) {
          $exclude_entities_batch['operations'][] = [[CohesionSyncRefreshCommands::class, 'excludeEntityTypes'], [$item, $this]];
        }
        batch_set($exclude_entities_batch);
        break;
    }

    // Run the batch process.
    $backend_batch_process_results = drush_backend_batch_process();
    if (array_key_exists('drush_batch_process_finished', $backend_batch_process_results)
      && $backend_batch_process_results['drush_batch_process_finished'] === TRUE) {
      ksort($backend_batch_process_results[0]);
      $batch = &batch_get();
      $batch = NULL;
      unset($batch);
      return json_encode($backend_batch_process_results[0]);
    }

    return FALSE;
  }

  /**
   * Batch dispatch submission finished callback.
   */
  public static function packageBuildFinished($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      $message = \Drupal::translation()
        ->formatPlural(count($results), 'Building Package Process Completed.', '@count Building Package Processes Completed.');
    }
    else {
      $message = t('Building Package Requirements Failed to Complete.');
    }

    // Display the message.
    \Drupal::messenger()->addMessage($message);
  }

  public function packageRequirements($cohesionRefreshSyncManager, $definition, &$context) {
    $entity_type_id = $definition['entity_type']->id();

    // Build the group data (sorted by label).
    $query = $definition['storage']->getQuery();
    if($definition['entity_type']->hasKey('label')) {
      $query->sort($definition['entity_type']->getKey('label'), 'ASC');
    }

    $ids = $query->execute();

    $groups = [];

    foreach ($definition['storage']->loadMultiple($ids) as $entity) {
      $group_label = $cohesionRefreshSyncManager->getGroupLabelFromUsage($definition['usage'], $entity, $definition['entity_type']->getPluralLabel()->__toString());
      $group_id = str_replace(' ', '_', $group_label);

      // iI the entity is a content template, we want to filter by only modified ones.
      if($entity_type_id != 'cohesion_content_templates' || $entity->isModified()) {
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

    $context['results'][] = $package_requirements_form[$entity_type_id];
    $context['message'] = t('Created @title', array('@title' => $package_requirements_form[$entity_type_id]['label']));
  }

  public function packageBuild($uuid, $meta, $excluded_entity_type_ids, $cohesionSyncRefreshCommands, &$context) {
    $source_entity_uuid = $uuid;
    $source_entity_type = $meta['type'];

    if ($source_entity = $cohesionSyncRefreshCommands->entityRepository->loadEntityByUuid($source_entity_type, $source_entity_uuid)) {
      // Get details about the source entity type.
      $source_entity_type_id = $source_entity->getEntityTypeID();
      $source_entity_type_label = ucfirst($cohesionSyncRefreshCommands->entityTypeManager->getDefinition($source_entity_type_id)->getPluralLabel()->__toString());

      // Set source entity type details in the form.
      $package_contents_form[$source_entity_type_id]['label'] = $source_entity_type_label;

      // Lop over the dependency groups.
      $dependency_groups = [];
      foreach ($cohesionSyncRefreshCommands->packagerManager->buildPackageEntityList([$source_entity], $excluded_entity_type_ids) as $dependency) {
        // Get the label of the entity type.
        $entity_type_label = ucfirst($cohesionSyncRefreshCommands->entityTypeManager->getDefinition($dependency['type'])->getPluralLabel()->__toString());

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

      $context['results'][] = $package_contents_form[$source_entity_type_id];
      $context['message'] = t('Created @title', array('@title' => $source_entity->label() ?? $source_entity->id()));
    }
  }

  /**
   * Batch to create exclude entity types array.
   */
  public function excludeEntityTypes($item, $cohesionSyncRefreshCommands, &$context) {
    if ($item['exportable']) {
      try {
        $excluded_entity_types_form[$item['entity_type']] = [
          'label' => $cohesionSyncRefreshCommands->entityTypeManager->getDefinition($item['entity_type'])->getPluralLabel()->__toString(),
        ];

        $context['results'][] = $excluded_entity_types_form[$item['entity_type']];
        $context['message'] = t('Created @title', array('@title' => $excluded_entity_types_form[$item['entity_type']]['label']));
      }
      catch (\Exception $e) {
        $this->logger()->error('Fetching Excluded entity types failed with error @error', ['@error' => $e->getMessage()]);
      }
    }
  }

}
