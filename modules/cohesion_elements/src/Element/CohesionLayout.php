<?php

namespace Drupal\cohesion_elements\Element;

use Drupal\Core\Render\Element\RenderElement;

/**
 * Provides a container element.
 *
 * @RenderElement("cohesion_layout")
 */
class CohesionLayout extends RenderElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $class = get_class($this);
    return [
      '#theme' => 'cohesion_layout',
      '#pre_render' => [
        [$class, 'preRenderCohesionLayout'],
      ],
    ];
  }

  /**
   * Prepare properties for output in the template.
   */
  public static function preRenderCohesionLayout($element) {
    return $element;
  }

}
