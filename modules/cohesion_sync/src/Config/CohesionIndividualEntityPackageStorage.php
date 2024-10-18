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
class CohesionIndividualEntityPackageStorage extends CohesionPackageStorageBase implements StorageInterface {

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
    ConfigManagerInterface $config_manager,
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
  public function read($name) {
    if ($this->exists($name)) {
      return $this->storage->read($name);
    }

    return FALSE;
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
    $required_drupal_config = $this->getConfigDependencies($site_studio_config);

    $all_config = array_unique(array_merge($site_studio_config, $required_drupal_config));

    // Make sure that listAll does not return config names that don't exist.
    $all_config = array_filter($all_config, function ($name) {
      return $this->exists($name) && $this->read($name) && $this->configStatus($name) !== FALSE;
    });

    return array_values($all_config);
  }

}
