<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;

/**
 * Element category base.
 *
 * @package Drupal\cohesion_elements\Entity
 */
abstract class ElementCategoryBase extends CohesionConfigEntityBase implements CohesionSettingsInterface, ElementCategoryInterface {

  /**
   * The machine name of this category.
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of this category.
   *
   * @var string
   */
  protected $label;

  /**
   * The class that represents the color of the component.
   *
   * @var string
   */
  protected $class;

  /**
   * The weight in the list.
   *
   * @var int
   */
  protected $weight;

  /**
   * {@inheritdoc}
   */
  public function getClass() {
    return $this->class;
  }

  /**
   * {@inheritdoc}
   */
  public function setClass($class) {
    $this->class = $class;
  }

  /**
   * Get the category weight.
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * Set the category color.
   *
   * @param $weight
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

  /**
   *
   */
  public function getApiPluginInstance() {}

  /**
   * {@inheritdoc}
   */
  public function hasGroupAccess() {
    // Access control.
    $access_key = 'access ' . $this->id() . ' ' . $this->getEntityTypeId() . ' group';

    return \Drupal::currentUser()->hasPermission($access_key) || \Drupal::currentUser()->hasPermission($this->getEntityType()->getAdminPermission());
  }

}
