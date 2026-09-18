<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_elements\Entity\ComponentCategory;
use Drupal\cohesion_elements\Entity\ComponentTag;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Url;

/**
 * Custom components service.
 *
 * @package Drupal\cohesion_elements
 */
class CustomComponentsService {

  const CUSTOM_COMPONENTS_CID = 'sitestudio.custom_components';
  const FORM_MODEL = [
    'canvas' => [],
    'componentForm' => [],
    'mapper' => [],
    'model' => [],
    'previewModel' => [],
    'variableFields' => [],
    'disabledNodes' => [],
    'meta' => [],
  ];

  /**
   * Custom component discovery service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentDiscoveryInterface
   */
  protected $customComponentDiscovery;

  /**
   * Array of custom components.
   *
   * @var array
   */
  protected $components = [];

  /**
   * Default ComponentCategory.
   *
   * @var \Drupal\cohesion_elements\Entity\ComponentCategory
   */
  protected $default_category;

  /**
   * Drupal Cache Backend service.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\cohesion_elements\CategoryRelationshipsManager
   */
  protected $categoryRelationshipsManager;

  /**
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * CustomComponentsService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\cohesion_elements\CustomComponentDiscoveryInterface $customComponentDiscovery
   *   Custom component discovery service.
   * @param \Drupal\cohesion_elements\CategoryRelationshipsManager $categoryRelationshipsManager
   *   Category relationships manager.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   Cache backend service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Database\Connection $database
   *   Database connection.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    CustomComponentDiscoveryInterface $customComponentDiscovery,
    CategoryRelationshipsManager $categoryRelationshipsManager,
    CacheBackendInterface $cacheBackend,
    RendererInterface $renderer,
    Connection $database,
  ) {
    $this->customComponentDiscovery = $customComponentDiscovery;
    $this->cacheBackend = $cacheBackend;
    $this->entityTypeManager = $entityTypeManager;
    $this->categoryRelationshipsManager = $categoryRelationshipsManager;
    $this->renderer = $renderer;
    $this->database = $database;
  }

  /**
   * Sort array of components by weight.
   *
   * @param $a
   * @param $b
   * @return int
   */
  public static function sortByWeight($a, $b) {
    $key = 'weight';
    $a_weight = $a[$key] ?? 0;
    $b_weight = $b[$key] ?? 0;

    return $a_weight <=> $b_weight;
  }

  /**
   * Gets custom components from cache or filesystem.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getComponents(): array {
    if (empty($this->components)) {
      $cached_custom_components = $this->cacheBackend->get(self::CUSTOM_COMPONENTS_CID);
      if (!isset($cached_custom_components->data)) {
        $custom_components = $this->buildComponentsList();
        $this->cacheBackend->set(self::CUSTOM_COMPONENTS_CID, $custom_components);
        $this->components = $custom_components;
      }
      else {
        $this->components = $cached_custom_components->data;
      }
    }

    uasort($this->components, [
      'Drupal\cohesion_elements\CustomComponentsService',
      'sortByWeight',
    ]);

    return $this->components;
  }

  /**
   *  Get a custom component via the machine name.
   *
   * @param $machine_name
   *
   * @return array|NULL
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getComponent($machine_name) {
    if ($allComponents = $this->getComponents()) {
      if (isset($allComponents[$machine_name])) {
        return $allComponents[$machine_name];
      }
    }

    return NULL;
  }

  /**
   *  Get custom components of a certain category.
   *
   * @param \Drupal\cohesion_elements\Entity\ComponentCategory $category
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getComponentsInCategory(ComponentCategory $category): array {
    $componentsInCategory = [];
    if ($allComponents = $this->getComponents()) {
      foreach ($allComponents as $component) {
        if ($component['category']->id() === $category->id()) {
          $componentsInCategory[] = $component;
        }
      }
    }

    return $componentsInCategory;
  }

  /**
   * @param $customComponent
   * @return \Drupal\cohesion_elements\Entity\Component
   */
  public function formatAsComponent($customComponent): Component {
    return new Component([
      'id' => $customComponent['machine_name'],
      'weight' => $this->getWeight($customComponent),
      'uid' => $customComponent['machine_name'],
      'label' => $customComponent['name'],
      'category' => $customComponent['category']->id(),
      'tag' => isset($customComponent['tag']) ? Component::getTagDetails($customComponent['tag'], FALSE) : [],
      'json_values' => json_encode($customComponent['form']),
      'isCustomComponent' => TRUE,
      'uuid' => $customComponent['machine_name'],
    ], 'cohesion_component');
  }

  /**
   * Format a list of custom component as "components" that can be used within
   * component lists.
   *
   * @param $customComponents
   *
   * @return array
   */
  public function formatListAsComponents($customComponents): array {
    $customComponentEntities = [];
    foreach ($customComponents as $customComponent) {
      $customComponentEntities[$customComponent['machine_name']] = $this->formatAsComponent($customComponent);
    }

    return $customComponentEntities;
  }

  /**
   * Return list of custom components formatted for elementActionAll()
   *
   * @param array $results
   * @param $type_access
   *  The entity type for which we want to load the custom components
   * @param $bundle_access
   *  The bundle for which we want to load the custom components
   *
   * @return mixed
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function patchComponentList(array &$results, $type_access, $bundle_access) {
    foreach ($this->getComponents() as $id => $component) {

      // Skip if the custom component is not available for this entity type
      // and bundle.
      if (!$this->customComponentListFilter($component, $type_access, $bundle_access)) {
        continue;
      }

      // Build the component.
      $results[$component['category']->id()]['children'][] = [
        'uid' => $id,
        'type' => 'component',
        'title' => $component['title'],
        'weight' => $this->getWeight($component),
        'enabled' => TRUE,
        'category' => $component['category']->get('class'),
        'tag' => isset($component['tag']) ? Component::getTagDetails($component['tag']) : [],
        'componentId' => $id,
        'componentType' => 'misc',
        'isCustomComponent' => TRUE,
        'preview_image' => $component['preview_image'],
      ];
    }
    return $results;
  }

  /**
   * Checks and performs filtering of custom component list by entity type and
   * bundle if necessary.
   *
   * @param $component
   * @param $type_access
   * @param $bundle_access
   *
   * @return bool
   */
  private function customComponentListFilter($component, $type_access, $bundle_access) {
    // No availability set - component is not limited to entity types/bundles.
    if (!isset($component['availability']) || $type_access == 'all' || $bundle_access == 'all') {
      return TRUE;
    }

    // Check availability properties against current type and bundle.
    foreach ($component['availability'] as $availability) {
      if (isset($availability['type']) && $availability['type'] === $type_access) {
        if (isset($availability['bundles']) && in_array($bundle_access, $availability['bundles'])) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Builds component list by using Component Discovery service.
   *
   * @return array
   */
  protected function buildComponentsList(): array {
    $components = [];

    foreach ($this->customComponentDiscovery->getComponents() as $id => $component) {
      if (isset($component['form'])) {
        $form_json = file_get_contents($component['path'] . $component['form']);
        if (!empty(json_decode($form_json, TRUE))) {
          $form = new LayoutCanvas($form_json);
        }
      }

      if (!isset($form)) {
        $form_json = json_encode(self::FORM_MODEL, JSON_FORCE_OBJECT);
        $form = new LayoutCanvas($form_json);
      }
      $components[$id] = $component;
      $components[$id]['title'] = $component['name'];
      $components[$id]['category'] = $this->getCategory($component['category']);
      $components[$id]['tag'] = isset($component['tag']) ? $this->getTags($component['tag']) : [];
      $components[$id]['weight'] = $this->getWeight($component);
      $components[$id]['form'] = $form;
      unset($form);
      if (isset($component['html'])) {
        $components[$id]['html'] = $component['path'] . $component['html'];
      }
      if (isset($component['template'])) {
        $components[$id]['template'] = preg_replace('/\.html\.twig$/', '', $component['template']);
      }
      $components[$id]['preview_image'] = [
        'url' => NULL,
      ];
      if (isset($component['preview_image'])) {
        $components[$id]['preview_image']['url'] = $component['subpath'] . $component['preview_image'];
      }
    }

    return $components;
  }

  /**
   * Gets ComponentCategory by id. If such category doesn't exist, returns
   * default category. If default category doesn't exist - creates and
   * returns default category.
   *
   * @param $category_id
   *  The category id
   *
   * @return \Drupal\cohesion_elements\Entity\ComponentCategory
   */
  private function getCategory($category_id) {

    if ($category = ComponentCategory::load($category_id)) {
      return $category;
    }
    else {
      if (is_null($this->default_category)) {
        $default_category = ComponentCategory::load(ComponentCategory::DEFAULT_CATEGORY_ID);
        if ($default_category === NULL) {
          $category_storage = $this->entityTypeManager->getStorage('cohesion_component_category');
          $this->categoryRelationshipsManager->createUncategorized($category_storage, ComponentCategory::DEFAULT_CATEGORY_ID);
          $this->default_category = ComponentCategory::load(ComponentCategory::DEFAULT_CATEGORY_ID);
        }
        else {
          $this->default_category = $default_category;
        }
      }

      return $this->default_category;
    }
  }

  /**
   * Gets ComponentTag entities by ID. If no tags exist, returns an empty array.
   *
   * @param array $tag_ids
   *   An array of tag IDs.
   *
   * @return \Drupal\cohesion_elements\Entity\ComponentTag[]|[]
   *   An array of ComponentTag entities, or an empty array if none are found.
   */
  private function getTags(array $tag_ids) {
    // Validate input: Ensure tag_ids is a non-empty array.
    if (empty($tag_ids)) {
      return [];
    }

    // Load and return tags, or return an empty array if none found.
    return ComponentTag::loadMultiple($tag_ids) ?: [];
  }

  /**
   * @param $component
   * @return array
   */
  public function getTagIds($component) {
    $tagIds = [];

    foreach ($component['tag'] as $tag) {
      $tagIds[] = $tag->id();
    }

    return $tagIds;
  }

  /**
   * Get the markup for in use.
   *
   * @return array
   *   A render array for the in use status.
   *
   * @throws \Drupal\Core\Entity\EntitymalformedException
   */
  public function getInUseMarkup($entity) {
    // Handle both array (custom component) and entity object inputs
    $id = is_array($entity) ? $entity['machine_name'] : $entity->id();

    if ($this->hasInUse($entity)) {
      return [
        '#type' => 'link',
        '#title' => t('In use'),
        '#url' => URL::fromRoute('custom_component.' . $id . '.in_use', [
          'machine_name' => $id,
        ]),
        '#options' => [
          'attributes' => [
            'class' => ['use-ajax'],
            'data-dialog-type' => 'modal',
            'data-dialog-options' => Json::encode([
              'width' => 700,
            ]),
          ],
        ],
        '#attached' => ['library' => ['core/drupal.dialog.ajax']],
      ];
    }
    else {
      return [
        '#markup' => t('Not in use'),
      ];
    }
  }

  /**
   * Check if a custom component is in use.
   *
   * @param mixed $component
   *   The custom component (can be array or Component entity).
   *
   * @return bool
   *   TRUE if the component is in use, FALSE otherwise.
   */
  public function hasInUse($component) {
    $machineName = is_array($component) ? $component['machine_name'] : $component->id();

    // Check the usage tracking table
    try {
      $query = $this->database->select('coh_usage', 'cu')
        ->fields('cu', ['source_uuid'])
        ->condition('cu.requires_uuid', $machineName, '=')
        ->condition('cu.requires_type', 'cohesion_component', '=');

      $usage = $query->countQuery()->execute()->fetchField();

      return $usage > 0;
    }
    catch (\Exception $e) {
      // If there's a database error, return FALSE
      return FALSE;
    }
  }

  /**
   * Get the custom components weight if defined or default to 0.
   *
   * @param $custom_component
   * @return int|mixed
   */
  public function getWeight($custom_component) {
    return $custom_component['weight'] ?? 0;
  }

  /**
   * Check if a component is a dropzone.
   */
  public function isDropzoneComponent($component) {
    // Handle both array and object inputs
    $json = is_array($component) ? ($component['json_values'] ?? NULL) : $component->get('json_values');
    if (is_null($json)) {
      return FALSE;
    }

    if (is_array($json)) {
      $json = json_encode($json);
    }

    return strpos($json, '"dropzoneHideSelector"') !== FALSE;
  }

}
