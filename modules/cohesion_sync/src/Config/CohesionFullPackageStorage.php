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
class CohesionFullPackageStorage extends CohesionPackageStorageBase implements StorageInterface {

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
    if (!is_null($entity_type) && in_array($entity_type, $this->getIncludedEntityTypes())) {
      return $this->storage->read($name);
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = 'cohesion') {
    $site_studio_config = $this->storage->listAll($prefix);
    $required_drupal_config = $this->getConfigDependencies($site_studio_config);

    $all_config = array_unique(array_merge($site_studio_config, $required_drupal_config));

    // Make sure that listAll does not return config names that don't exist.
    $all_config = array_filter($all_config, function ($name) {
      return $this->exists($name) && $this->read($name) && $this->configStatus($name) !== FALSE;
    });

    return array_values($all_config);
  }

}
