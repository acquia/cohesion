<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\cohesion_sync\Entity\Package;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Read only storage for SiteStudio packages.
 *
 * @package Drupal\cohesion_sync\Config
 */
class CohesionPackageStorage implements StorageInterface {

  /**
   * The configuration storage to be cached.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * SiteStudio Package Entity.
   *
   * @var \Drupal\cohesion_sync\Entity\Package
   */
  protected $package;

  /**
   * Included items list.
   *
   * @var array
   */
  protected $includedList = [];

  /**
   * Files.
   *
   * @var array
   */
  public $files = [];

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * Constructs a new CachedStorage.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage to be cached.
   * @param \Drupal\cohesion_sync\Entity\Package $package
   *   SiteStudio Package Entity.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   */
  public function __construct(
    StorageInterface $storage,
    Package $package,
    ConfigManagerInterface $config_manager
  ) {
    $this->storage = $storage;
    $this->package = $package;
    $this->configManager = $config_manager;
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name): bool {
    return $this->storage->exists($name);
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {
    if ($this->exists($name)) {
      return $this->storage->read($name);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names): array {
    $list = [];
    foreach ($names as $name) {
      if ($data = $this->read($name)) {
        $list[$name] = $data;
      }
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data): string {
    return $this->storage->encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    return $this->storage->decode($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = 'cohesion') {
    $usage_list = \Drupal::service('database')->select('coh_usage', 'c1')
      ->fields('c1', ['requires_uuid', 'requires_type'])
      ->condition('c1.source_uuid', $this->package->uuid(), '=')
      ->execute()
      ->fetchAllKeyed();

    $usage_list[$this->package->uuid()] = $this->package->getEntityTypeId();
    $all_cohesion_config = $this->storage->listAll($prefix);

    $data = [];
    foreach ($all_cohesion_config as $name) {
      $config = $this->storage->read($name);
      if ((!isset($config['status']) || $config['status'] == TRUE) && isset($usage_list[$config['uuid']])) {
        $data[] = $name;
      }
    }
    $data = array_unique($data);

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    return $this->storage->createCollection($collection);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    return $this->storage->getAllCollectionNames();
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName(): string {
    return $this->storage->getCollectionName();
  }

  /**
   * Builds a list of files that Site Studio config has dependencies on.
   *
   * @throws \Exception
   */
  protected function buildStorageFileList() {
    $this->files = [];

    $excluded_entity_types = $this->package->getExcludedEntityTypes();

    // Build the excluded entity types up.
    $excluded_entity_type_ids = [];
    foreach ($excluded_entity_types as $entity_type_id => $excluded) {
      if ($excluded) {
        $excluded_entity_type_ids[] = $entity_type_id;
      }
    }

    foreach ($this->storage->listAll() as $name) {
      if ($this->storage->exists($name)) {
        $entity_type = $this->configManager->getEntityTypeIdByName($name);
        if (!in_array($entity_type, $excluded_entity_type_ids)) {
          $config = $this->storage->read($name);
          if (!isset($config['status']) || $config['status'] == TRUE) {
            if (!empty($config['dependencies']['content'])) {
              foreach ($config['dependencies']['content'] as $dependency) {
                if (strpos($dependency, 'file:file:') !== FALSE) {
                  $file = explode(':', $dependency);
                  $this->files[$file[2]] = $file[0];
                }
              }
            }
          }
        }
      }
    }
  }

  /**
   * Gets array of file uuids.
   *
   * @return array
   *   Array of files Site Studio config has dependencies on.
   *
   * @throws \Exception
   */
  public function getStorageFileList() {
    if ($this->files === NULL) {
      $this->buildStorageFileList();
    }

    return $this->files;
  }

}
