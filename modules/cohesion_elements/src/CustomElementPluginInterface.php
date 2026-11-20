<?php

namespace Drupal\cohesion_elements;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Custom element plugin interface.
 *
 * @package Drupal\cohesion_elements
 */
interface CustomElementPluginInterface extends PluginInspectionInterface, ContainerFactoryPluginInterface {

  /**
   * Get the label to be used for the custom element in UI.
   *
   * @return mixed
   */
  public function getLabel();

  /**
   * Get the form fields that make up this element's options.
   *
   * @return array
   */
  public function getFields();

  /**
   * Render the element.
   *
   * @param $element_settings
   *   Contains an array of form settings as defined by the user/site builder.
   * @param $element_markup
   *   Contains an array of markup settings as defined by the user/site builder.
   * @param $element_class
   *   The class of this element (if the user/site builder added settings to
   *   the styles tab, this class can be used to target that CSS).
   *
   * @return array
   */
  public function render($element_settings, $element_markup, $element_class);

}
