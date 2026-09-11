<?php

namespace Drupal\cohesion_elements\Entity;

/**
 * Element category interface.
 */
interface ElementCategoryInterface {

  /**
   * Does the currect user has permissions to access this category?
   * (Used for sidebar browser and list builder access mostly).
   *
   * @return bool
   */
  public function hasGroupAccess();

  /**
   * Get the category color.
   */
  public function getClass();

  /**
   * Set the category color.
   *
   * @param $class
   */
  public function setClass($class);

}
