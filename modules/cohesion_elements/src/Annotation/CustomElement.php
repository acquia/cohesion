<?php

namespace Drupal\cohesion_elements\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a custom element.
 *
 * @package Drupal\cohesion_elements\Annotation
 *
 * @Annotation
 */
class CustomElement extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label to use for the custom element.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
