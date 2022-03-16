<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\cohesion\UsagePluginManager;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Cohesion Storage for full import/export.
 *
 * This is a read only storage
 *
 * @package Drupal\cohesion_sync\Config
 */
class CohesionFullPackageStorage implements StorageInterface {

  /**
   * The configuration storage to be cached.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $storage;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * Array of files that Site Studio config has dependencies on.
   *
   * @var array
   */
  protected $files;

  /**
   * Array of entity types to be included when performing a full export.
   *
   * @var array
   */
  protected $includedEntityTypes;

  /**
   * The usage pluging manager service
   *
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * Constructs a new CachedStorage.
   *
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage to be cached.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Drupal\cohesion\UsagePluginManager $usage_plugin_manager
   *   The usage plugin manager service.
   */
  public function __construct(StorageInterface $storage, ConfigManagerInterface $config_manager, UsagePluginManager $usage_plugin_manager) {
    $this->storage = $storage;
    $this->configManager = $config_manager;
    $this->usagePluginManager = $usage_plugin_manager;
  }

  /**
   * Gets array of file uuids.
   *
   * @return array
   *   Array of files Site Studio config has dependencies on.
   */
  public function getStorageFileList() {
    if ($this->files === NULL) {
      $this->buildStorageFileList();
    }

    return $this->files;
  }

  /**
   * Builds a list of files that Site Studio config has dependencies on.
   */
  protected function buildStorageFileList() {
    $this->files = [];

    foreach ($this->listAll() as $name) {
      $config = $this->read($name);
      if ($this->configStatus($name) == TRUE) {
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

  /**
   * {@inheritdoc}
   */
  public function exists($name) {
    return $this->storage->exists($name);
  }

  /**
   * Gets entity types to be included in full package export.
   *
   * @return array
   *   Array of included entity types.
   *
   * @throws \Exception
   */
  public function getIncludedEntityTypes(): array {
    if (!$this->includedEntityTypes) {
      $enabled_entity_types = $this->usagePluginManager->getEnabledEntityTypes();
      if (empty($enabled_entity_types)) {
        throw new \Exception('Export settings have not been defined (enabled_entity_types configuration not found). Visit: /admin/cohesion/sync/export_settings to configure package export.');
      }
      // Build the excluded entity types up.
      $this->includedEntityTypes = [];
      foreach ($enabled_entity_types as $entity_type_id => $enabled) {
        if ((bool) $enabled !== FALSE) {
          $this->includedEntityTypes[] = $entity_type_id;
        }
      }
    }

    return $this->includedEntityTypes;
  }

  /**
   * {@inheritdoc}
   */
  public function read($name) {
    $entity_type = $this->configManager->getEntityTypeIdByName($name);
    if (in_array($entity_type, $this->getIncludedEntityTypes())) {
      return $this->storage->read($name);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names) {
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
  public function write($name, array $data) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name) {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data) {
    return $this->storage->encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw) {
    return $this->storage->decode($raw);
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
  public function listAll($prefix = 'cohesion') {
    $site_studio_config = $this->storage->listAll($prefix);
    $required_drupal_config = $this->buildDependencies($site_studio_config);

    $all_config = array_unique(array_merge($site_studio_config, $required_drupal_config));

    // Make sure that listAll does not return config names that don't exist.
    $all_config = array_filter($all_config, function ($name) {
      return $this->exists($name) && $this->read($name) && $this->configStatus($name) !== FALSE;
    });

    return array_values($all_config);
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
  public function getCollectionName() {
    return $this->storage->getCollectionName();
  }

}
