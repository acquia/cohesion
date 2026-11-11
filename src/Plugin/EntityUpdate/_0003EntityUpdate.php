<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Component\Plugin\PluginBase;

/**
 * Remove componentContentId from component inside ComponentContent.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0003",
 * )
 */
class _0003EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof CohesionLayout && $entity->getParentEntity() instanceof ComponentContent) {
      $this->updateEntity($entity);
    }

    return TRUE;
  }

  /**
   * @param \Drupal\cohesion_elements\Entity\CohesionLayout $entity
   */
  public function updateEntity(&$entity) {
    if ($json_values = $entity->getDecodedJsonValues(TRUE)) {
      if (property_exists($json_values, 'canvas') && is_array($json_values->canvas) && count($json_values->canvas) == 1 &&
        property_exists($json_values->canvas[0], 'componentId') && property_exists($json_values->canvas[0], 'componentContentId')) {
        unset($json_values->canvas[0]->componentContentId);
        $entity->setJsonValue(json_encode($json_values));
      }
    }
  }

}
