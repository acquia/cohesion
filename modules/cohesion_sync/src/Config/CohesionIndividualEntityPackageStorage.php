<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Read only storage for SiteStudio packages based on individual entities.
 *
 * @package Drupal\cohesion_sync\Config
 */
class CohesionIndividualEntityPackageStorage implements StorageInterface {

  /**
   * The configuration storage to be cached.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * SiteStudio Config Entity.
   *
   * @var \Drupal\cohesion\Entity\CohesionConfigEntityBase
   */
  protected $entity;

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
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   */
  public function __construct(
    StorageInterface $storage,
    ConfigManagerInterface $config_manager
  ) {
    $this->storage = $storage;
    $this->configManager = $config_manager;
  }

  /**
   * Sets cohesion config entity.
   *
   * @param \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity
   *   Cohesion config entity.
   */
  public function setEntity(CohesionConfigEntityBase $entity) {
    $this->entity = $entity;
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
   * Lists all config for a specific config entity.
   *
   * @param string $prefix
   *   Config prefix.
   *
   * @return array
   *   Array of config entity names.
   */
  public function listAll($prefix = 'cohesion') {
    if (!$this->entity instanceof CohesionConfigEntityBase) {
      return [];
    }
    $site_studio_config = $this->entity->getDependencies()['config'] ?? [];
    $site_studio_config[] = $this->entity->getConfigDependencyName();
    $required_drupal_config = $this->buildDependencies($site_studio_config);

    $all_config = array_unique(array_merge($site_studio_config, $required_drupal_config));

    // Make sure that listAll does not return config names that don't exist.
    $all_config = array_filter($all_config, function ($name) {
      return $this->exists($name) && $this->read($name) && $this->configStatus($name) !== FALSE;
    });

    return array_values($all_config);
  }

  /**
   * Builds a list of config names based on Site Studio config dependencies.
   *
   * @param array $site_studio_config
   *   List of site studio config entity names.
   *
   * @return array
   *   List of required config names.
   */
  protected function buildDependencies(array $site_studio_config): array {
    $dependencies = [];

    foreach ($site_studio_config as $name) {
      if ($this->exists($name)) {
        $config = $this->read($name);
        if (isset($config['dependencies']['config'])) {
          $dependencies = array_merge($dependencies, $config['dependencies']['config']);
        }
      }
    }

    return array_unique($dependencies);
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
   * Gets array of file uuids.
   *
   * @throws \Exception
   */
  public function getStorageFileList() {
    if (empty($this->files)) {
      $this->buildStorageFileList();
    }

    return $this->files;
  }

  /**
   * Builds a list of files that Site Studio config has dependencies on.
   *
   * @throws \Exception
   */
  protected function buildStorageFileList() {
    $this->files = [];

    foreach ($this->listAll() as $name) {
      if ($this->storage->exists($name)) {
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

  /**
   * Returns the status of config, if config has status.
   *
   * @param string $name
   *   Config name.
   *
   * @return bool
   *   True if status property exists and is set to True.
   */
  protected function configStatus(string $name): bool {
    $config = $this->read($name);

    return (!isset($config['status']) || $config['status'] == TRUE);
  }

}
