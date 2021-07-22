<?php

namespace Drupal\cohesion_elements;

use Drupal\Component\Plugin\PluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for a custom element.
 *
 * @package Drupal\cohesion_elements
 */
abstract class CustomElementPluginBase extends PluginBase implements CustomElementPluginInterface {

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel() {
    return $this->pluginDefinition['label'];
  }

  /**
   * @return array
   */
  public function getFields() {
    return [];
  }

  /**
   * The render function for this element. Should return a render array.
   *
   * @param $element_settings
   * @param $element_markup
   * @param $element_class
   *
   * @return array
   */
  public function render($element_settings, $element_markup, $element_class) {
    return [];
  }

  /**
   * Return the default element settings.
   *
   * @return array
   */
  public function buildDefaultModelSettings() {
    $settings = [];

    foreach ($this->getFields() as $key => $field) {
      if (isset($field['defaultValue'])) {
        $settings[$key] = $field['defaultValue'];
      }
    }

    return [
      'settings' => (object) $settings,
    ];
  }

}
