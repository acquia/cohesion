<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Annotation\Usage;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Site studio Usage plugin manager.
 *
 * @package Drupal\cohesion
 */
class UsagePluginManager extends DefaultPluginManager {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Exportable entities by site studio sync/package.
   *
   * @var array
   */
  protected $exportableEntities = NULL;

  /**
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $config_storage;

  /**
   * UsageManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Config\StorageInterface $storage
   *   A configuration storage to be cached.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler, EntityTypeManagerInterface $entity_type_manager, StorageInterface $storage) {
    parent::__construct('Plugin/Usage', $namespaces, $module_handler, UsagePluginInterface::class, Usage::class);

    $this->setCacheBackend($cache_backend, 'cohesion_usage');
    $this->alterInfo('cohesion_usage');
    $this->entityTypeManager = $entity_type_manager;
    $this->config_storage = $storage;
  }

  /**
   * Get the list of exportable entities.
   * These are defined by annotation on their Usage class.
   *
   * @return array
   */
  public function getExportableEntities() {
    if (is_null($this->exportableEntities)) {
      $this->exportableEntities = [];
      foreach ($this->getDefinitions() as $item) {
        if ($item['exportable']) {
          try {
            $this->exportableEntities[$item['entity_type']] = [
              'name' => ucfirst($this->entityTypeManager->getDefinition($item['entity_type'])->getPluralLabel()->__toString()),
            ];
          }
          catch (\Throwable $e) {
            continue;
          }
        }
      }
    }

    return $this->exportableEntities;
  }

  /**
   * Get the cohesion sync settings.
   *
   * @return array|bool
   */
  private function getCohesionSyncSettings() {
    return $this->config_storage->read('cohesion.sync.settings');
  }

  /**
   * Get the entity types enabled for full export.
   *
   * @return array
   *   The enabled entity types.
   */
  public function getEnabledEntityTypes() {
    $config_settings = $this->getCohesionSyncSettings();
    if ($entity_types = is_array($config_settings) && isset($config_settings['enabled_entity_types']) ? $config_settings['enabled_entity_types'] : NULL) {
      $entity_types = array_filter($entity_types, function ($key) {
        return isset($this->getExportableEntities()[$key]);
      }, ARRAY_FILTER_USE_KEY);
    }
    else {
      $entity_types = [];
    }

    return $entity_types;
  }

}
