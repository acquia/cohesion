<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Annotation\Api;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 *
 */
class ApiPluginManager extends DefaultPluginManager {

  /**
   * ApiManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Api', $namespaces, $module_handler, ApiPluginInterface::class, Api::class);

    $this->setCacheBackend($cache_backend, 'cohesion_api');
    $this->alterInfo('cohesion_api');
  }

}
