<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update component form selects schema types.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0011",
 * )
 */
class _0011EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

        foreach ($layoutCanvas->iterateModels('component_form') as $model) {
          if ($model->getProperty(['settings', 'type']) == 'cohSelect') {
            $schema = $model->getProperty(['settings', 'schema']);
            $schema_type = $model->getProperty(['settings', 'schema', 'type']);
            if ($schema && !$schema_type) {
              $schema_type = $model->getProperty(['settings', 'schema']);
              $json_values->model->{$model->getUUID()}->settings->schema = new \stdClass();
              $json_values->model->{$model->getUUID()}->settings->schema->type = $schema_type;
            }

          }
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

}
