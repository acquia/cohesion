<?php

namespace Drupal\cohesion\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines an ImageBrowser attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ImageBrowser extends Plugin {

  /**
   * Constructs a new ImageBrowser attribute.
   *
   * @param string $id
   * The plugin ID.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $name,
    public readonly string $module,
  ) {}

}
