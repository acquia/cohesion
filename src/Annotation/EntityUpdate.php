<?php

namespace Drupal\cohesion\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a reusable form plugin annotation object.
 *
 * @package Drupal\cohesion\Annotation;
 *
 * @Annotation
 */
class EntityUpdate extends Plugin {

  /**
   * Should be in the format: "entityupdate_xxxx" where xxxx is numerical.
   *
   * @var string
   */
  public $id;

}
