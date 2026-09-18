<?php

namespace Drupal\cohesion\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a reusable form plugin annotation object.
 *
 * @package Drupal\cohesion\Annotation;
 *
 * @deprecated in cohesion:8.2.8 and is removed from cohesion:8.3.0.
 * Use the "\Drupal\cohesion\Attribute\ImageBrowser" PHP attribute instead.
 *
 * @Annotation
 */
class ImageBrowser extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The name of the form plugin.
   *
   * @var \Drupal\Core\Annotation\Translation
   *
   * @ingroup plugin_translatable
   */
  public $name;

  /**
   * The machine name of the module this plugin requires.
   *
   * @var string
   */
  public $module;

}
