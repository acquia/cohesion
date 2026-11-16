<?php

namespace Drupal\cohesion_sync;

use Drupal\cohesion_sync\Annotation\Sync;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Sync plugin manager.
 *
 * @package Drupal\cohesion_sync
 */
class SyncPluginManager extends DefaultPluginManager {

  /**
   * SyncPluginManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Sync', $namespaces, $module_handler, SyncPluginInterface::class, Sync::class);

    $this->setCacheBackend($cache_backend, 'cohesion_sync');
    $this->alterInfo('cohesion_sync');
  }

}
