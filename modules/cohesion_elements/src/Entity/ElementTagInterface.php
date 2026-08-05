<?php

namespace Drupal\cohesion_elements\Entity;

/**
 * Element tags interface.
 */
interface ElementTagInterface {

  /**
   * Does the currect user has permissions to access this tag?
   * (Used for sidebar browser and list builder access mostly).
   *
   * @return bool
   */
  public function hasGroupAccess();

  /**
   * Get the tag color.
   */
  public function getClass();

  /**
   * Set the tag color.
   *
   * @param $class
   */
  public function setClass($class);

}
