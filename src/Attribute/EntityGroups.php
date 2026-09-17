<?php

namespace Drupal\cohesion\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an EntityGroups attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EntityGroups extends Plugin {

  /**
   * Constructs a new EntityGroups attribute.
   *
   * @param string $id
   * The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $name
   * The name of the form plugin.
   * @param string $entity_type
   * The entity type name that this plugin works for.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $name,
    public readonly string $entity_type,
  ) {}

}
