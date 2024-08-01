<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update cohTypeahead endpoint.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0013",
 * )
 */
class _0013EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
          if ($model->getProperty(['settings', 'type']) == 'cohTypeahead') {
            $json_values->model->{$model->getUUID()}->settings->endpoint = '/cohesionapi/link-autocomplete?q=';
          }
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

}
