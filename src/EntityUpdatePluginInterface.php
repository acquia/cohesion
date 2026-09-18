<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Defines an interface for entity updates.
 *
 * @package Drupal\cohesion
 */
interface EntityUpdatePluginInterface extends PluginInspectionInterface {

  /**
   * Return the name of the reusable form plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Run the update on the provided entity. Return TRUE to apply the update.
   * If return FALSE then the update will try and apply every time the
   * entity is saved (useful for testing).
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function runUpdate(&$entity);

}
