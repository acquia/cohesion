<?php

namespace Drupal\cohesion_sync\Entity;

/**
 * Interface PackageSettingsInterface.
 *
 * @package Drupal\cohesion_sync\Entity
 */
interface PackageSettingsInterface {

  /**
   * Getter.
   *
   * @return mixed
   */
  public function getExcludedEntityTypes();

  /**
   * Setter.
   *
   * @param $excluded_entity_types
   *
   * @return mixed
   */
  public function setExcludedEntityTypes($excluded_entity_types);

  /**
   * Getter.
   *
   * @return mixed
   */
  public function getSettings();

  /**
   * Setter.
   *
   * @param $settings
   *
   * @return mixed
   */
  public function setSettings($settings);

}
