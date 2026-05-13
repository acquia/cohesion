<?php

namespace Drupal\cohesion_elements\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

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
  public string $id;

  /**
   * The label to use for the custom element.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public Translation $label;

  /**
   * If the custom element is a container.
   *
   * @var bool
   */
  public bool $container = FALSE;

}
