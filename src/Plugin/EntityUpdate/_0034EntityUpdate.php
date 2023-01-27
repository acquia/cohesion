<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Remove isContainer from Layout canvas JSON data.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0034",
 * )
 */
class _0034EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        $json_values = $entity->getDecodedJsonValues(TRUE);

        // Remove from Layout canvas JSON.
        foreach ($layoutCanvas->iterateCanvas() as $element) {
          $element->unsetProperty('isContainer');
        }

        // Remove from Component form.
        if (property_exists($json_values, 'componentForm')) {
          foreach ($layoutCanvas->iterateComponentForm() as $element) {
            $element->unsetProperty('isContainer');
          }
        }

        // Remove from Style guide form.
        if (property_exists($json_values, 'styleGuideForm')) {
          foreach ($layoutCanvas->iterateStyleGuideForm() as $element) {
            $element->unsetProperty('isContainer');
          }
        }

        $entity->setJsonValue(json_encode($layoutCanvas));
      }
    }
    return TRUE;
  }

}
