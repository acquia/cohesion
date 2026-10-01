<?php

namespace Drupal\cohesion_sync\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a sync attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Sync extends Plugin {

  /**
   * Constructs a new Sync attribute.
   *
   * @param string $id
   * The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $name
   * The name of the sync plugin.
   * @param string $interface
   * The entity interface this plugin works for ($entity implements xxxx).
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $name,
    public readonly string $interface,
  ) {}

}
