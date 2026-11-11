<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update Image uploader field element.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0010",
 * )
 */
class _0010EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        foreach ($layoutCanvas->iterateModels('component_form') as $model) {
          // If the component form element is a WYSIWYG and has a value update
          // to the new model.
          if ($model->getProperty(['settings', 'type']) == 'cohFileBrowser') {
            if ($model->getProperty(['settings', 'schema', 'type']) == 'image') {
              $json_values->model->{$model->getUUID()}->settings->schema->type = 'string';
            }

          }
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

}
