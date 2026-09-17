<?php

namespace Drupal\cohesion_elements\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines a CustomElement attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CustomElement extends Plugin {

  /**
   * Constructs a new CustomElement attribute.
   *
   * @param string $id
   * The plugin ID.
   * @param \Drupal\Core\StringTranslation\TranslatableMarkup $label
   * The label of the custom element.
   * @param bool $container
   * Whether this element is a container for other elements. Defaults to FALSE.
   */
  public function __construct(
    public readonly string $id,
    public readonly TranslatableMarkup $label,
    public readonly bool $container = FALSE,
  ) {}

}
