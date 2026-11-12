<?php

namespace Drupal\cohesion\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Example configuration override.
 */
class CKeditorStylesOverrides implements ConfigFactoryOverrideInterface {

  const REQUIRED_MODULES = ['ckeditor5', 'cohesion_custom_styles'];

  /**
   * Holds the module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The config factory service
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   A config factory for retrieving required config objects.
   */
  public function __construct(ModuleHandlerInterface $moduleHandler, ConfigFactoryInterface $config_factory) {
    $this->moduleHandler = $moduleHandler;
    $this->configFactory = $config_factory;
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names) {

    $overrides = [];

    foreach (self::REQUIRED_MODULES as $module) {
      if ($this->moduleHandler->moduleExists($module) === FALSE) {
        return $overrides;
      }
    }

    /** @var \Drupal\cohesion\Services\TextFormatStyles $textFormatStyles */
    $textFormatStyles = \Drupal::service('cohesion.text_format_styles');

    foreach ($names as $name) {
      if (strpos($name, 'editor.editor.') === 0) {
        $config = $this->configFactory->getEditable($name);
        $ssa_enabled = $config->get('third_party_settings.cohesion.ssa_enabled');
        if ($ssa_enabled && $styles = $config->get('settings.plugins.ckeditor5_style.styles')) {
          $overrides['editor.editor.cohesion']['settings']['plugins']['ckeditor5_style']['styles'] = $textFormatStyles->getStyleList($styles, $ssa_enabled);
        }
      }
    }

    return $overrides;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix() {
    return 'CKeditorStylesOverrider';
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name) {
    return new CacheableMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

}
