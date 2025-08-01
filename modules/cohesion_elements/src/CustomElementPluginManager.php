<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion_elements\Annotation\CustomElement;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 * Custom element plugin manager.
 *
 * @package Drupal\cohesion_elements
 */
class CustomElementPluginManager extends DefaultPluginManager {

  /**
   * CustomElementPluginManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/CustomElement', $namespaces, $module_handler, CustomElementPluginInterface::class, CustomElement::class);

    $this->setCacheBackend($cache_backend, 'dx8_elements');
    $this->alterInfo('dx8_elements');
  }

}
