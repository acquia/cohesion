<?php

namespace Drupal\cohesion\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Defines an EntityUpdate attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class EntityUpdate extends Plugin {

  /**
   * Constructs a new EntityUpdate attribute.
   *
   * @param string $id
   * The plugin ID - Should be in the format: "entityupdate_xxxx" where
   * xxxx is numerical.
   */
  public function __construct(
    public readonly string $id,
    public readonly bool $requireAPI = TRUE,
  ) {}

}
