<?php

namespace Drupal\cohesion_website_settings\Entity;

/**
 * Website settings source trait.
 *
 * @package Drupal\cohesion_website_settings\Entity
 */
trait WebsiteSettingsSourceTrait {

  /**
   * "uploadFonts" | "webFonts".
   *
   * @var string
   */
  protected $source;

  /**
   * Getter.
   *
   * @return int
   */
  public function getSource() {
    return $this->source ? $this->source : NULL;
  }

  /**
   * Setter.
   *
   * @param $source
   */
  public function setSource($source) {
    $this->source = $source;
  }

}
