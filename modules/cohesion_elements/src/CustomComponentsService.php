<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\ComponentCategory;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

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
   * CustomComponentsService constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   Entity type manager.
   * @param \Drupal\cohesion_elements\CustomComponentDiscoveryInterface $customComponentDiscovery
   *   Custom component discovery service.
   * @param \Drupal\cohesion_elements\CategoryRelationshipsManager $categoryRelationshipsManager
   *   Category relationships manager.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    CustomComponentDiscoveryInterface $customComponentDiscovery,
    CategoryRelationshipsManager $categoryRelationshipsManager,
    CacheBackendInterface $cacheBackend
  ) {
    $this->customComponentDiscovery = $customComponentDiscovery;
    $this->cacheBackend = $cacheBackend;
    $this->entityTypeManager = $entityTypeManager;
    $this->categoryRelationshipsManager = $categoryRelationshipsManager;
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
        'enabled' => TRUE,
        'category' => $component['category']->get('class'),
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

}
