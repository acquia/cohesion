<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Annotation\ImageBrowser;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;

/**
 *
 */
class ImageBrowserPluginManager extends DefaultPluginManager {

  /**
   * ImageBrowserPluginManager constructor.
   *
   * @param \Traversable $namespaces
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    parent::__construct('Plugin/ImageBrowser', $namespaces, $module_handler, ImageBrowserPluginInterface::class, ImageBrowser::class);

    $this->setCacheBackend($cache_backend, 'cohesion_image_browser');
    $this->alterInfo('cohesion_image_browser');
  }

}
