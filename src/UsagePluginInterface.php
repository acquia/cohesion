<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Entity\EntityInterface;

/**
 * Defines Usage Plugin Interface.
 *
 * @package Drupal\cohesion
 */
interface UsagePluginInterface extends PluginInspectionInterface {

  /**
   * Return the name of the reusable form plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Get the entity type this plugin works for from the annotation.
   *
   * @return mixed
   */
  public function getEntityType();

  /**
   * Exposes the underlying JSON and other data from the entity to other Usage
   * plugins.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function getScannableData(EntityInterface $entity);

  /**
   * Given some scannable data, search for instances of this type of entity
   * (ie. cohesion_custom_style).
   *
   * @param $data
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity);

}
