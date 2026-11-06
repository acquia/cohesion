<?php

namespace Drupal\cohesion\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a Usage attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Usage extends Plugin {

  /**
   * Constructs a new Usage attribute.
   *
   * @param string $id
   * The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $name
   * The name of the plugin.
   * @param string $entity_type
   * The entity type that this plugin works for.
   * @param bool $scannable
   * The entity that this plugin works for can have dependencies (FALSE for
   * things like WebsiteSettings colors and WebsiteSettings icons).
   * @param bool $scan_same_type
   * The UsageUpdateManager can scan entities of the same type with the same
   * plugin (nesting).
   * @param string|false $group_key
   * When grouping entities of this type on the package form, which entity
   * key should be used to group by?
   * This can be a comma separated list if there are more than one (ie. for
   * content templates).
   * @param string|false $group_key_entity_type
   * The entity type that the group key belongs to if different from the entity
   * type of the plugin.
   * @param bool $exclude_from_package_requirements
   * Whether to exclude this plugin from package requirements when it is
   * scanned as a dependency.
   * @param bool $exportable
   * Whether entities of this type should be included in the full site export
   * using sync.
   * @param string $config_type
   * This is used to group Cohesion and core entities together in the package
   * requirements on the sync package page.
   * @param array $scan_groups
   * Groups plugins to decide if they should be scanned for dependencies.
   * @param bool $can_be_excluded
   * Whether entities of this type should be included in the exclude list on
   * package form.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $name,
    public readonly string $entity_type,
    public readonly bool $scannable,
    public readonly bool $scan_same_type,
    public readonly string|false $group_key,
    public readonly string|false $group_key_entity_type,
    public readonly bool $exclude_from_package_requirements,
    public readonly bool $exportable,
    public readonly string $config_type,
    public readonly bool $can_be_excluded,
    public readonly array $scan_groups = [],
  ) {}

}
