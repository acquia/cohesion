<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\TemplateStorage\TemplateStorageBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\file\Entity\File;

/**
 * Defines the Site Studio component entity.
 */
abstract class CohesionElementEntityBase extends CohesionConfigEntityBase implements CohesionSettingsInterface, CohesionElementSettingsInterface {

  /**
   * @var null
   */
  protected $category = NULL;

  /**
   * @var null
   */
  protected $preview_image = NULL;

  /**
   * @var array
   */
  protected $entity_type_access = [];

  /**
   * @var array
   */
  protected $bundle_access = [];

  /**
   * Component weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * Component weight getter.
   *
   * @return int
   */
  public function getWeight() {
    return $this->weight ?: 0;
  }

  /**
   * Get helper category.
   */
  public function getCategory() {
    return $this->category;
  }

  /**
   * Set entity category.
   *
   * @param string $category
   *   Entity category.
   *
   * @return string
   *   Return the set entity category.
   */
  public function setCategory($category) {
    return $this->category = $category;
  }

  /**
   * Get the type of category entity for this entity.
   *
   * @return mixed
   */
  public function getCategoryEntityTypeId() {
    return get_called_class()::CATEGORY_ENTITY_TYPE_ID;
  }

  /**
   * Get the entity category referenced in theis entity's 'category' field.
   *
   * @return array|bool|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getCategoryEntity() {
    try {
      $storage = \Drupal::entityTypeManager()->getStorage($this->getCategoryEntityTypeId());
    }
    catch (\Throwable $e) {
      return [];
    }

    // And return the entity.
    if ($entity = $storage->load($this->getCategory())) {
      return $entity;
    }
    else {
      return FALSE;
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getAssetName() {
    return self::getAssetGroupId();
  }

  /**
   * Get component entity type access.
   */
  public function getEntityTypeAccess() {
    return $this->entity_type_access;
  }

  /**
   * Set component entity type access.
   *
   * @param array $entity_type_access
   *   Entity type access.
   *
   * @return array
   *   Return the set component entity type access.
   */
  public function setEntityTypeAccess($entity_type_access) {
    return $this->entity_type_access = $entity_type_access;
  }

  /**
   * Get component bundle access.
   */
  public function getBundleAccess() {
    return $this->bundle_access;
  }

  /**
   * Set component bundle access.
   *
   * @param string $bundle_access
   *   Bundle access.
   *
   * @return string
   *   Return the set component bundle access.
   */
  public function setBundleAccess($bundle_access) {
    return $this->bundle_access = $bundle_access;
  }

  /**
   * Get the entity type/bundle availability.
   *
   * @return array
   */
  public function getAvailabilityData() {
    $merge_bundles = [];
    $types = is_array($this->get('entity_type_access')) ? array_filter($this->get('entity_type_access')) : [];
    $bundles = is_array($this->get('bundle_access')) ? array_filter($this->get('bundle_access')) : [];

    if ($bundles) {
      foreach ($bundles as $bundles_data) {
        if (is_array($bundles_data)) {
          $merge_bundles = array_merge($merge_bundles, $bundles_data);
        }
      }
    }
    return [$types, $merge_bundles];
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    parent::setDefaultValues();
    $this->category = '';
    $this->preview_image = '';
    $this->entity_type_access = [];
    $this->bundle_access = [];
  }

  /**
   * Get preview image.
   */
  public function getPreviewImage() {
    try {
      if ($file_entities = \Drupal::entityTypeManager()
        ->getStorage('file')
        ->loadByProperties(['uri' => $this->preview_image])) {
        if (count($file_entities)) {
          return key($file_entities);
        }
      }

    }
    catch (\Exception $e) {
    }

    return NULL;
  }

  /**
   * Set preview image.
   *
   * @param int $preview_image
   *   Reference to the preview image file in the managed files table.
   *
   * @return int
   *   Return a reference to the preview image file in the managed files table.
   */
  public function setPreviewImage($preview_image) {
    return $this->preview_image = $preview_image;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $this->preProcessJsonValues();
    /*
     * Manage preview image usage.
     */
    $preview_image = $this->getPreviewImage() ? $this->getPreviewImage() : NULL;
    $preview_image_original = NULL;
    if ($preview_image && $this->getOriginalId() && $helper_original = self::load($this->getOriginalId())) {
      $preview_image_original = $helper_original->getPreviewImage();
    }

    // Remove old preview images - update usage and delete file if necessary.
    if ($preview_image_original && $preview_image_original != $preview_image) {
      $file = File::load($preview_image_original);
      $file_usage = \Drupal::service('file.usage');
      $file_usage->delete($file, 'cohesion', $this->getEntityType()
        ->id(), $this->id());
      if (!$file_usage->listUsage($file)) {
        $file->delete();
      }
    }

    // Add new preview images - make uploaded file permanent and update usage.
    if ($preview_image !== NULL && $preview_image_original != $preview_image) {
      /** @var \Drupal\file\Entity\File $file */
      $file = File::load($preview_image);
      $file->setPermanent();
      $file->save();

      $file_usage = \Drupal::service('file.usage');
      $file_usage->add($file, 'cohesion', $this->getEntityType()
        ->id(), $this->id());
    }

    // Get the twig filename.
    $filename_prefix = 'component' . TemplateStorageBase::TEMPLATE_PREFIX;
    $filename = $filename_prefix . str_replace('_', '-', str_replace('cohesion-helper-', '', $this->get('id')));
    $this->set('twig_template', $filename);
  }

  /**
   * {@inheridoc}.
   */
  public function process() {
    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');

    // Don't attempt to convert to "Uncategorized" if we're importing or
    // updating.
    if (!$cohesion_sync_lock) {
      $category_class = \Drupal::entityTypeManager()->getStorage($this->getCategoryEntityTypeId())->getEntitytype()->getOriginalClass();
      \Drupal::service('cohesion_elements.category_relationships')->processCategory($this->getCategory(), $this->getCategoryEntityTypeId(), $category_class);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    // Remove preview images - update usage and delete file if necessary.
    foreach ($entities as $entity) {
      if ($preview_image = $entity->getPreviewImage()) {
        $file = File::load($preview_image);

        // Check the file exists. This will fix deletion for some legacy
        // components that erroneously have null file associations.
        if ($file) {
          $file_usage = \Drupal::service('file.usage');
          $file_usage->delete($file, 'cohesion', $entity->getEntityType()
            ->id(), $entity->id());
          if (!$file_usage->listUsage($file)) {
            $file->delete();
          }
        }
      }
    }
  }

  /**
   * @inheritDoc
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('templates_api');
  }

  /**
   * @return array|bool|void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function jsonValuesErrors() {
    if ($this->json_values === '{}') {
      return FALSE;
    }

    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $success = $send_to_api->sendWithoutSave();
    $responseData = $send_to_api->getData();

    if ($success === TRUE) {
      return FALSE;
    }
    else {
      return $responseData;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function isLayoutCanvas() {
    return TRUE;
  }

  /**
   * Determine if this is a standard layout canvas or a form canvas.
   *
   * @return bool|string
   */
  public function getTopType() {
    $json_values = $this->getDecodedJsonValues();
    $top_uid = FALSE;
    $top_type = FALSE;
    $top_component_type = FALSE;

    if (isset($json_values['canvas'])) {
      foreach ($json_values['canvas'] as $top_level_element) {
        if (isset($top_level_element['uid'])) {
          if ($top_uid === FALSE) {
            $top_uid = $top_level_element['uid'];
            $top_type = $top_level_element['type'] ?? FALSE;
            $top_component_type = $top_level_element['componentType'] ?? FALSE;
          }
          elseif ($top_uid !== $top_level_element['uid']) {
            $top_uid = 'misc';
            $top_type = FALSE;
            $top_component_type = FALSE;

            $form_delimiter = 'form-';
            if (isset($top_level_element['type']) && substr($top_level_element['type'], 0, strlen($form_delimiter)) == $form_delimiter) {
              $top_uid = "{$form_delimiter}{$top_uid}";
            }
          }
        }
      }
    }

    // If the top element was a component, return the underlying component
    // top type, otherwise just return what we found as the top type in
    // this entity.
    return $top_component_type == FALSE ? $top_uid : $top_component_type;
  }

}
