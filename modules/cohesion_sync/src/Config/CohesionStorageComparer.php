<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;

/**
 * Storage comparer for Site studio sync
 */
class CohesionStorageComparer extends StorageComparer {

  /**
   * The usage update manager service.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * Constructs the Configuration storage comparer.
   *
   * @param \Drupal\Core\Config\StorageInterface $source_storage
   *   Storage object used to read configuration.
   * @param \Drupal\Core\Config\StorageInterface $target_storage
   *   Storage object used to write configuration.
   * @param \Drupal\cohesion\UsageUpdateManager $usageUpdateManager
   *   Usage Update Manager service.
   */
  public function __construct(StorageInterface $source_storage, StorageInterface $target_storage, UsageUpdateManager $usageUpdateManager) {
    parent::__construct($source_storage, $target_storage);
    $this->usageUpdateManager = $usageUpdateManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getEmptyChangelist() {
    $list = parent::getEmptyChangelist();
    $list['locked'] = [];

    return $list;
  }

  /**
   * {@inheritdoc}
   */
  protected function addChangelistCreate($collection) {
    parent::addChangelistCreate($collection);

    $this->sortCustomStyles($collection, 'create');
  }

  /**
   * Sort the custom styles, delete duplicate with same class name
   *
   * @param $collection
   * @param $op
   */
  private function sortCustomStyles($collection, $op) {
    foreach ($this->changelist[$collection][$op] as $name) {
      // For each new custom style entity that share the same
      // class name as another in the active storage
      // remove the custom style in active storage
      // unless the locked
      if ($this->isCustomStyle($name)) {
        $source_config = $this->sourceStorage->read($name);
        if (isset($source_config['class_name'])) {
          $this->manageCustomStyleDelete($collection, $op, $name, $source_config['class_name']);
        }
      }
    }
  }

  /**
   * Manage change list for custom styles in create operation
   *
   * @param $collection
   *  The collection name
   * @param $op
   *  The operation (create, update)
   * @param $name
   *  The name of the config to be created.
   * @param $class_name
   *   The class name of the config to be created
   */
  private function manageCustomStyleDelete($collection, $op, $name, $class_name) {
    if (!in_array($name, $this->changelist[$collection][$op])) {
      return;
    }

    // Loop over all active storage config
    foreach ($this->targetNames[$collection] as $target_name) {
      // Treat only custom styles
      if ($this->isCustomStyle($target_name)) {
        $target_config = $this->sourceStorage->read($target_name);
        // Custom style should have a class name and be the same as the target
        if (isset($target_config['class_name']) && $class_name == $target_config['class_name'] && $name !== $target_name) {
          // If the entity is not locked on active storage, delete it.
          // otherwise remove it from the operation
          if (!isset($target_config['locked']) || $target_config['locked'] == FALSE) {
            $this->addChangeList($collection, 'delete', [$target_name]);
          } else {
            $this->removeFromChangelist($collection, $op, $name);
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function addChangelistUpdate($collection) {
    parent::addChangelistUpdate($collection);

    $this->sortCustomStyles($collection, 'update');

    foreach ($this->changelist[$collection]['update'] as $name) {
      $update_config = $this->targetStorage->read($name);
      if (isset($update_config['locked']) && $update_config['locked'] === TRUE) {
        $this->removeFromChangelist($collection, 'update', $name);
        $this->addChangeList($collection, 'locked', [$name]);
      }
    }
  }

  /**
   * Checks if there are any operations with changes to process and locked
   * changed entities.
   *
   * @return bool
   */
  public function hasChangesWithLocked() {
    foreach ($this->getAllCollectionNames() as $collection) {
      foreach (['locked'] as $op) {
        if (!empty($this->changelist[$collection][$op])) {
          return TRUE;
        }
      }
    }

    return parent::hasChanges();
  }

  /**
   * Returns list of config entities that are in use and will be deleted
   */
  public function getInuseDelete() {
    $in_use_to_rebuild = [];

    $changes = $this->getChangelist();
    if (isset($changes['delete'])) {
      foreach ($changes['delete'] as $name) {
        $config = $this->getTargetStorage()->read($name);
        if(isset($config['uuid'])) {
          $in_use_to_rebuild = array_merge($in_use_to_rebuild, $this->usageUpdateManager->getInUseEntitiesListByUuid($config['uuid']));
        } else {
          throw new \Exception("The config '$name' cannot be deleted as it is missing its UUID and will still be tracked as in use. Please make sure the config contains a UUID before imporing");
        }
      }
    }

    return $in_use_to_rebuild;
  }

  /**
   * Check if a config name is a site studio custom style
   *
   * @param $name
   *  A config name
   *
   * @return bool
   */
  private function isCustomStyle($name) {
    [, $target_entity_type] = explode('.', $name);
    // Treat only custom styles
    return $target_entity_type == 'cohesion_custom_style';
  }

}
