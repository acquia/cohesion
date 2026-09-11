<?php

namespace Drupal\cohesion\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines a reusable form plugin annotation object.
 *
 * @package Drupal\cohesion\Annotation;
 *
 * @deprecated in cohesion:8.2.8 and is removed from cohesion:8.3.0.
 * Use the "\Drupal\cohesion\Attribute\EntityGroups" PHP attribute instead.
 *
 * @Annotation
 */
class EntityGroups extends Plugin {

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
   * The entity type name that this plugin works for.
   *
   * @var string
   */
  public $entity_type;

}
