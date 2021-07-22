<?php

namespace Drupal\cohesion_elements\Element;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\FormElement;

/**
 * Select from a list of css classes.
 *
 * @FormElement("color_class_radios")
 */
class ColorClassRadios extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#input' => TRUE,
      '#process' => [
        [$class, 'processColor'],
      ],
      '#element_validate' => [
        [$class, 'validateColor'],
      ],
      '#theme' => 'form_color_class_radios',
      '#theme_wrappers' => ['form_element'],
      '#attached' => [
        'library' => ['file/drupal.file'],
      ],
      '#accept' => NULL,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function processColor(&$element, FormStateInterface $form_state, &$complete_form) {
    // Make sure defaut value is not null.
    if ($element['#default_value'] == NULL) {
      $element['#default_value'] = '';
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function validateColor(&$element, FormStateInterface $form_state, &$complete_form) {
    if (empty($element['#value'])) {
      $form_state->setValueForElement($element, "");
    }
  }

}
