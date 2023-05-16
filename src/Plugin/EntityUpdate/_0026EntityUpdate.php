<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update breakpointClasses.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0026",
 * )
 */
class _0026EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

      $json_values = $entity->getDecodedJsonValues(TRUE);

      if ($entity->isLayoutCanvas()) {
        $layoutCanvas = $entity->getLayoutCanvasInstance();

        foreach ($layoutCanvas->iterateCanvas() as &$element) {
          if ($element->getProperty('breakpointClasses')) {
            $element->unsetProperty('breakpointClasses');
          }
        }

        $entity->setJsonValue(json_encode($layoutCanvas));
      }

    }

    return TRUE;
  }

}
