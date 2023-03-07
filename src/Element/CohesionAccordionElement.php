<?php

namespace Drupal\cohesion\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a collapsible accordion element.
 *
 * @RenderElement("cohesion_accordion")
 */
class CohesionAccordionElement extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#theme' => 'cohesion_accordion',
      '#title' => 'Accordion title',
      '#pre_render' => [
        [$class, 'preRenderCohesionAccordionElement'],
      ],
    ];
  }

  /**
   * Prepare the render array for the template.
   */
  public static function preRenderCohesionAccordionElement($element) {
    // Attach library.
    $element['#attached'] = [
      'library' => [
        'cohesion/cohesion-accordion-element',
      ],
    ];

    return $element;
  }

}
