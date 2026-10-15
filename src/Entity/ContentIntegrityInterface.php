<?php

namespace Drupal\cohesion\Entity;

/**
 * Interface checkContentIntegrity
 *
 * Provide an interface for entity that require content integrity check
 *
 * @package Drupal\cohesion\Entity
 */
interface ContentIntegrityInterface {

  /**
   * Check whether the content defined where this entity has been used is
   * still usable Content may not be usable anymore (and might be lost) if a
   * component field has been remove.
   *
   * @param $json_values
   *   NULL|string - Values to check against if not using the
   *   stored values
   *
   * @return array - the list of entities with broken integrity
   */
  public function checkContentIntegrity($json_values);

}
