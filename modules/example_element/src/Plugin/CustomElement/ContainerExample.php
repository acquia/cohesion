<?php

namespace Drupal\example_element\Plugin\CustomElement;

use Drupal\cohesion_elements\CustomElementPluginBase;

/**
 * Container HTML element plugin for Site Studio.
 *
 * @package Drupal\cohesion\Plugin\CustomElement
 *
 * @CustomElement(
 *   id = "example_container_element",
 *   label = @Translation("Example container element"),
 *   container = true
 * )
 */
class ContainerExample extends CustomElementPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getFields() {
    return [
      'mytextfield' => [
        // This is the bootstrap class name that will be applied to the
        // wrapping column.
        'htmlClass' => 'ssa-grid-col-12',
        // All form elements require a title.
        'title' => 'Title of my text field.',
        // The field type.
        'type' => 'textfield',
        // These fields are specific to this form field type.
        'placeholder' => 'Placeholder text.',
        'required' => TRUE,
        'validationMessage' => 'This field is required.',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function render($element_settings, $element_markup, $element_class, $element_context = [], $element_children = '') {
    // Render the element.
    return [
      '#theme' => 'example_container_element',
      '#template' => 'example-container-element-template',
      '#elementSettings' => $element_settings,
      '#elementMarkup' => $element_markup,
      '#elementContext' => $element_context,
      '#elementClass' => $element_class,
      '#elementChildren' => $element_children,
    ];
  }

}
