<?php

namespace Drupal\cohesion\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an api attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Api extends Plugin {

  /**
   * Constructs a new Api attribute.
   *
   * @param string $id
   * The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $name
   * The name of the api plugin.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $name,
  ) {}

}
