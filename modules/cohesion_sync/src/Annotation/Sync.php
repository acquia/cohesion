<?php

namespace Drupal\cohesion_sync\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a reusable form plugin annotation object.
 *
 * @package Drupal\cohesion_sync\Annotation
 *
 * @Annotation
 */
class Sync extends Plugin {

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
   * The entity interface this plugin works for ($entity implements xxxx)
   *
   * @var string
   */
  public $interface;

}
