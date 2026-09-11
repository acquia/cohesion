<?php

namespace Drupal\cohesion\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a reusable form plugin annotation object.
 *
 * @package Drupal\cohesion\Annotation;
 *
 * @deprecated in cohesion:8.2.8 and is removed from cohesion:8.3.0.
 * Use the "\Drupal\cohesion\Attribute\EntityUpdate" PHP attribute instead.
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
