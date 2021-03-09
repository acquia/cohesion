<?php

namespace Drupal\cohesion;

use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\cohesion\Annotation\Usage;

/**
 * Class UsagePluginManager.
 *
 * @package Drupal\cohesion
 */
class UsagePluginManager extends DefaultPluginManager {

  /**
   * UsageManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/Usage', $namespaces, $module_handler, UsagePluginInterface::class, Usage::class);

    $this->setCacheBackend($cache_backend, 'cohesion_usage');
    $this->alterInfo('cohesion_usage');
  }

}
