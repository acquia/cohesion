<?php

namespace Drupal\cohesion_elements\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Defines a custom element.
 *
 * @package Drupal\cohesion_elements\Annotation
 *
 * @deprecated in cohesion:8.2.8 and is removed from cohesion:8.3.0.
 * Use the "\Drupal\cohesion_elements\Attribute\CustomElement" PHP attribute
 * instead.
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
