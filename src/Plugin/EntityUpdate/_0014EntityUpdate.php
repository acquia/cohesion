<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update Link element convert to container.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0014",
 * )
 */
class _0014EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof EntityJsonValuesInterface) {
      if ($entity->isLayoutCanvas()) {
        $layoutCanvas = $entity->getLayoutCanvasInstance();

        foreach ($layoutCanvas->iterateCanvas() as $element) {
          if ($element->getProperty('uid') == 'link') {
            $element->setProperty('isContainer', TRUE);
            $element->setProperty('type', 'container');
            $element->setProperty('children', []);
            $status = $element->getProperty('status');
            if (is_object($status)) {
              $status->collapsed = TRUE;
            }
            $element->setProperty('status', $status);
          }
        }

        $entity->setJsonValue(json_encode($layoutCanvas));
      }

    }

    return TRUE;
  }

}
