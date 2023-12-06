<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Resave all config entities to re-run dependency calculation.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0035",
 * )
 */
class _0035EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function runUpdate(&$entity) {
    if (method_exists($entity, 'calculateDependencies')) {
      $entity->calculateDependencies();
    }
    return TRUE;
  }

}
