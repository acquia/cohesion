<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\cohesion_sync\Entity\Package;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Read only storage for SiteStudio packages.
 *
 * @package Drupal\cohesion_sync\Config
 */
class CohesionPackageStorage implements StorageInterface {

  use DependencySerializationTrait;

  /**
   * The configuration storage to be cached.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * SiteStudio Package metadata.
   *
   * @var \Drupal\cohesion_sync\Entity\Package
   */
  protected $package;

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
   * Database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new CachedStorage.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage to be cached.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database connection.
   */
  public function __construct(
    StorageInterface $storage,
    ConfigManagerInterface $config_manager,
    Connection $connection,
  ) {
    $this->storage = $storage;
    $this->configManager = $config_manager;
    $this->connection = $connection;
  }

  /**
   * Sets Package.
   *
   * @param \Drupal\cohesion_sync\Entity\Package $package
   *   Package entity.
   */
  public function setPackage(Package $package) {
    $this->package = $package;
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
  public function listAll($prefix = '') {
    $usage_list = $this->connection->select('coh_usage', 'c1')
      ->fields('c1', ['requires_uuid', 'requires_type'])
      ->condition('c1.source_uuid', $this->package->uuid(), '=')
      ->execute()
      ->fetchAllKeyed();

    $usage_list[$this->package->uuid()] = $this->package->getEntityTypeId();
    $all_config = $this->storage->listAll($prefix);

    $data = [];
    foreach ($all_config as $name) {
      $config = $this->storage->read($name);
      if (isset($config['uuid']) && isset($usage_list[$config['uuid']])) {
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

    $usage_list = $this->connection->select('coh_usage', 'c1')
      ->fields('c1', ['requires_uuid'])
      ->condition('c1.source_uuid', $this->package->uuid(), '=')
      ->condition('c1.requires_type', 'file', '=')
      ->execute()
      ->fetchAllAssoc('requires_uuid');

    foreach ($usage_list as $uuid) {
      $this->files[$uuid->requires_uuid] = 'file';
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
    if (empty($this->files)) {
      $this->buildStorageFileList();
    }

    return $this->files;
  }

}
