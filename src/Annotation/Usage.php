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
class Usage extends Plugin {

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
   * The entity type that this plugin works for.
   *
   * @var string
   */
  public $entity_type;

  /**
   * The entity that this plugin works for can have dependencies (FALSE for
   * things like WebsiteSettings colors and WebsiteSettings icons).
   *
   * @var bool
   */
  public $scannable;

  /**
   * The UsageUpdateManager can scan entities of the same type with the same
   * plugin (nesting).
   *
   * @var bool
   */
  public $scan_same_type;

  /**
   * When grouping entities of this type on the package form, which entity
   * key should be used to group by?
   *
   * This can be a comma separated list if there are more than one (ie. for
   * content templates).
   *
   * @var string
   */
  public $group_key;

  /**
   * If group_key is a reference to an entity, what is it's type? If group_key
   * does not reference an entity, put FALSE here.
   *
   * This can be a comma separated list if there are more than one (ie. for
   * content templates).
   *
   * @var string
   */
  public $group_key_entity_type;

  /**
   * Does this plugin entity type appears as selectable on PackageForm?
   *
   * @var bool
   */
  public $exclude_from_package_requirements;

  /**
   * Entity exportable or not.
   *
   * Whether entities of this type should be included in the full site export
   * using sync.
   *
   * @var bool
   */
  public $exportable;

  /**
   * Is this a Site Studio or Core sync plugin.
   *
   * This is used to group Cohesion and core entities together in the package
   * requirements on the sync package page.
   *
   * @var string
   */
  public $config_type;

  /**
   * Groups plugins to decide if they should be scanned for dependencies.
   *
   * @var array
   */
  public $scan_groups = [];

  /**
   * Entity can be excluded.
   *
   * Whether entities of this type should be included in the exclude list on
   * package form.
   *
   * @var bool
   */
  public $can_be_excluded;

}
