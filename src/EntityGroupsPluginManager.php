<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Annotation\EntityGroups;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 *
 */
class EntityGroupsPluginManager extends DefaultPluginManager {

  /**
   * EntityGroupsPluginManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/EntityGroups', $namespaces, $module_handler, EntityGroupsPluginInterface::class, EntityGroups::class);

    $this->setCacheBackend($cache_backend, 'cohesion_entity_groups');
    $this->alterInfo('cohesion_entity_groups');
  }

}
