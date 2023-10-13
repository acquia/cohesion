<?php

namespace Drupal\cohesion\Entity;

use Drupal\cohesion\EntityJsonValuesTrait;
use Drupal\cohesion\EntityUpdateInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines a base configuration entity class.
 *
 * @ingroup entity_api
 */
abstract class CohesionConfigEntityBase extends ConfigEntityBase implements CohesionSettingsInterface, EntityUpdateInterface {

  use EntityJsonValuesTrait;

  // When styles are saved for this entity, this is the message.
  const STYLES_UPDATED_SAVE_MESSAGE = 'Your styles have been updated.';

  const ENTITY_MACHINE_NAME_PREFIX = '';

  /**
   * The Site Studio website settings ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Site Studio website settings label.
   *
   * @var string
   */
  protected $label;

  /**
   * The Site Studio website settings values.
   *
   * @var string
   */
  protected $json_values = '{}';

  /**
   * The Site Studio website settings mapper.
   *
   * @var string
   */
  protected $json_mapper = '{}';

  /**
   * The modified status of the entity, only turns to TRUE on form submit.
   *
   * @var bool
   */
  protected $modified = FALSE;

  /**
   * {@inheritdoc}
   */
  protected $status = TRUE;

  /**
   * @var bool
   */
  protected $selectable = TRUE;

  /**
   * The list of callbacks from \Drupal\cohesion\EntityUpdateManager that have
   * been applied to this entity.
   *
   * @var array
   */
  protected $last_entity_update = NULL;

  /**
   * Can the entity be overridden by Sync?
   *
   * @var array
   */
  protected $locked = FALSE;

  /**
   * {@inheritdoc}
   */
  public function getJsonValues() {
    return $this->json_values;
  }

  /**
   * {@inheritdoc}
   */
  public function getJsonMapper() {
    return $this->json_mapper;
  }

  /**
   * {@inheritdoc}
   */
  public function getDecodedJsonMapper() {
    try {
      return json_decode($this->getJsonMapper());
    }
    catch (\Exception $e) {
      return [];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setJsonValue($json_values) {
    $this->set('json_values', $json_values);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setJsonMapper($json_mapper) {
    $this->set('json_mapper', trim($json_mapper));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigType() {
    return $this->getEntityTypeId();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfigItemId() {
    return crc32($this->getEntityTypeId() . '_' . $this->id());
  }

  /**
   * @return bool
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Check and return the modified status of the entity.
   *
   * @return bool
   */
  public function isModified() {
    return $this->modified;
  }

  /**
   * Set the modified status of the entity.
   *
   * @param $modified
   *
   * @return $this
   */
  public function setModified($modified = TRUE) {
    $this->set('modified', (bool) $modified);
    return $this;
  }

  /**
   * Check and return the selectable status of the entity.
   *
   * @return bool
   */
  public function isSelectable() {
    return $this->selectable;
  }

  /**
   * Set the selectable status of the entity.
   *
   * @param $selectable
   *
   * @return $this
   */
  public function setSelectable($selectable = TRUE) {
    $this->set('selectable', (bool) $selectable);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function isLocked() {
    return $this->locked;
  }

  /**
   * {@inheritdoc}
   */
  public function setLocked($locked) {
    $this->set('locked', $locked);
    return $this;
  }

  /**
   * Return the asset group associated with this entity.
   *
   * @return string
   */
  public static function getAssetGroupId() {
    return get_called_class()::ASSET_GROUP_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    \Drupal::service('cohesion.entity_update_manager')->apply($this);

    // If the entity type can't be enabled or disable force setting status to
    // TRUE.
    if (!$this->getEntityType()->hasKey('status')) {
      $this->enable();
    }

    // Move any temporary files to cohesion.
    $decoded_json_values = $this->getDecodedJsonValues(TRUE);
    if (!empty($decoded_json_values)) {
      \Drupal::service('cohesion.local_files_manager')->moveTemporaryFiles($decoded_json_values);
      $this->setJsonValue(json_encode($decoded_json_values));
    }

    parent::preSave($storage);

    // If the entity is disabled set selectable as disabled as well.
    if (!$this->getStatus()) {
      $this->setSelectable(FALSE);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {

    // Update the requires table for this entity.
    \Drupal::service('cohesion_usage.update_manager')->buildRequires($this);

    // Clear all values if disable (remove css / template)
    if (!$this->getStatus() && $this->isModified()) {
      $this->clearData();
    }
    parent::postSave($storage, $update);
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    foreach ($entities as $entity) {
      if ($entity->isUninstalling() || $entity->isSyncing()) {
        // During extension uninstall and configuration synchronization
        // deletions are already managed.
        break;
      }
      // Fix or remove any dependencies.
      $config_entities = static::getConfigManager()->getConfigEntitiesToChangeOnDependencyRemoval('config', [$entity->getConfigDependencyName()], FALSE);
      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $dependent_entity */
      foreach ($config_entities['update'] as $dependent_entity) {
        $dependent_entity->save();
      }
    }

    foreach ($entities as $entity) {
      // Clear all values if disable (remove css / template).
      $entity->clearData();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    foreach ($entities as $entity) {
      $config_entities = \Drupal::service('config.manager')
        ->findConfigEntityDependenciesAsEntities('config', [$entity->getConfigDependencyName()]);
      \Drupal::service('cohesion_usage.update_manager')->removeUsage($entity);
      /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $dependent_entity */
      $dx8_no_send_to_api = &drupal_static('dx8_no_send_to_api');
      $retain_value = $dx8_no_send_to_api;
      $dx8_no_send_to_api = TRUE;
      foreach ($config_entities as $dependent_entity) {
        if ($dependent_entity instanceof CohesionConfigEntityBase) {
          $dependent_entity->calculateDependencies();
          $dependent_entity->save();
        }
      }
      $dx8_no_send_to_api = $retain_value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    // Set default entity values.
    $this->json_values = '{}';
    $this->json_mapper = '{}';

    $this->modified = FALSE;
    $this->status = FALSE;
  }

  /**
   * Get all CohesionConfigEntity entities.
   *
   * @param bool $enabled
   *
   * @return array
   */
  public static function getAll($enabled = TRUE) {
    $entities = [];
    $entity_defs = \Drupal::service('entity_type.manager')->getDefinitions();
    $config_entities = array_keys($entity_defs);

    foreach ($config_entities as $entity_id) {
      if (strpos($entity_id, 'cohesion_') !== FALSE) {
        if ($storage = \Drupal::service('entity_type.manager')->getStorage($entity_id)) {
          if ($enabled) {
            $ids = $storage->getQuery()->accessCheck(TRUE)->condition('status', $enabled)->execute();
          }
          else {
            $ids = $storage->getQuery()->accessCheck(TRUE)->execute();
          }
          $ids = array_keys($ids);

          if ($ids) {
            $entities = $entities + $storage->loadMultiple($ids);
          }
        }
      }
    }
    return $entities;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAppliedUpdate() {
    return $this->last_entity_update;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastAppliedUpdate($callback) {
    $this->set('last_entity_update', $callback);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasInUse() {
    // If this entity has an in-use route...
    if ($this->getEntityType()->hasLinkTemplate('in-use')) {
      // Check if it's in use on any entities.
      return \Drupal::service('cohesion_usage.update_manager')->hasInUse($this);
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMarkup() {
    if ($this->hasInUse()) {
      $markup = [
        '#type' => 'link',
        '#title' => t('In use'),
        '#url' => $this->toUrl('in-use'),
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
      $markup = [
        '#markup' => t('Not in use'),
      ];
    }

    return \Drupal::service('renderer')->render($markup);
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [];
  }

  /**
   * @inheritdoc
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    // Add entity id as a parameter for the in use route.
    if ($rel == 'in-use') {
      $uri_route_parameters[$this->getEntityTypeId()] = $this->id();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->delete();
  }

  /**
   * {@inheritdoc}
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();

    $duplicate->setModified(FALSE);
    $duplicate->setStatus(FALSE);
    return $duplicate;
  }

  /**
   * @param $entity
   */
  protected static function clearCache($entity) {
    // Clear the theme registry cache.
    \Drupal::service('theme.registry')->reset();
  }

  /**
   * @return \Drupal\cohesion\ApiPluginBase|void
   */
  public function process() {
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
  }

  /**
   * {@inheritdoc}
   */
  public function isLayoutCanvas() {
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityMachineNamePrefix() {
    // If the entity already exists and doesn't contain the prefix, don't use
    // the prefix.
    if ($this->id !== NULL && substr($this->id, 0, strlen($this::ENTITY_MACHINE_NAME_PREFIX)) !== $this::ENTITY_MACHINE_NAME_PREFIX) {
      return '';
    }
    // Otherwise use the prefix.
    else {
      return $this::ENTITY_MACHINE_NAME_PREFIX;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function canEditMachineName() {
    return $this->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    // All dependencies should be recalculated on every save apart from enforced
    // dependencies. This ensures stale dependencies are never saved.
    $this->dependencies = array_intersect_key($this->dependencies, ['enforced' => '']);

    $dependencies = \Drupal::service('cohesion_usage.update_manager')->getDependencies($this);
    foreach ($dependencies as $dependency) {
      $plugin = \Drupal::entityTypeManager()->getDefinition($dependency['type']);
      $entity = \Drupal::entityTypeManager()
        ->getStorage($dependency['type'])
        ->loadByProperties([$plugin->getKey('uuid') => $dependency['uuid']]);
      $entity = reset($entity);
      if ($entity instanceof EntityInterface) {
        $this->addDependency($entity->getConfigDependencyKey(), $entity->getConfigDependencyName());
      }
    }

    return $this;
  }

}
