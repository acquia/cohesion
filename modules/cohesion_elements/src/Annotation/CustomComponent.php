<?php

namespace Drupal\cohesion_elements\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a custom component.
 *
 * @package Drupal\cohesion_elements\Annotation
 *
 * @Annotation
 */
class CustomComponent extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The label to use for the custom component.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $label;

}
