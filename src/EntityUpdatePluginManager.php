<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Annotation\EntityUpdate;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 *
 */
class EntityUpdatePluginManager extends DefaultPluginManager {

  /**
   * EntityUpdatePluginManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EntityUpdate', $namespaces, $module_handler, EntityUpdatePluginInterface::class, EntityUpdate::class);

    $this->setCacheBackend($cache_backend, 'cohesion_entity_update');
    $this->alterInfo('cohesion_entity_update');
  }

}
