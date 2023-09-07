<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Component and SGM field group - add `Enable padding` toggle option.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0021",
 * )
 */
class _0021EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

        // Update component field default values.
        foreach ($layoutCanvas->iterateModels() as $model) {
          $this->updateEnablePadding($model, $json_values);
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $model
   * @param $json_values
   */
  private function updateEnablePadding($model, &$json_values) {
    if ($model->getProperty(['settings', 'hideRowHeading']) !== NULL) {
      $property = ['settings', 'hideRowHeading'];
      $json_values->model->{$model->getUUID()}->settings->removePadding = $model->getProperty($property);
    }
  }

}
