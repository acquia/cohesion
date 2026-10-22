<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Interface CohesionElementSettingsInterface
 * Provides an interface for defining Site Studio element configuration
 * entities.
 *
 * @package Drupal\cohesion_elements\Entity
 */
interface CohesionElementSettingsInterface extends ConfigEntityInterface {

  /**
   * @return mixed
   */
  public function getCategory();

  /**
   * @return mixed
   */
  public function getCategoryEntity();

  /**
   * @param $category
   *
   * @return mixed
   */
  public function setCategory($category);

  /**
   * @return mixed
   */
  public function getPreviewImage();

  /**
   * @param $preview_image
   *
   * @return mixed
   */
  public function setPreviewImage($preview_image);

  /**
   * Get the entity asset name (overridden for helpers).
   *
   * @return string
   */
  public function getAssetName();

}
