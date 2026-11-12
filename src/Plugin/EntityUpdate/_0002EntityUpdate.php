<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\Component\Plugin\PluginBase;

/**
 * Extended custom styles should reference parents by classname instead of ID.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0002",
 * )
 */
class _0002EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof CustomStyle) {
      if ($parent_id = $entity->getParent()) {
        // Get the classname from the parent.
        if ($parent_entity = $this->getCustomStyle($parent_id)) {
          // Set the parent in the extended entity to be this class.
          $entity->setParent($parent_entity->getClass());
        }
      }
    }

    return TRUE;
  }

  /**
   * @param $parent_id
   *
   * @return \Drupal\cohesion_custom_styles\Entity\CustomStyle|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getCustomStyle($parent_id) {
    return CustomStyle::load($parent_id);
  }

}
