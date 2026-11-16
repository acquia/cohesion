<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update layout canvases to support the new variableFields.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0015",
 * )
 */
class _0015EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        $json_values = $entity->getDecodedJsonValues(TRUE);
        $layoutCanvas = $entity->getLayoutCanvasInstance();

        foreach ($layoutCanvas->iterateModels('canvas') as $model_uuid => $model) {
          $paths = [];
          $this->recurseModelData($model->getValues(), $paths);
          if (!empty($paths)) {
            if (!property_exists($json_values, 'variableFields')) {
              $json_values->variableFields = new \stdClass();
            }
            $json_values->variableFields->{$model_uuid} = $paths;
          }
        }

        $entity->setJsonValue(json_encode($json_values));
      }
    }

    return TRUE;
  }

  /**
   * @param $model_data
   * @param $paths
   * @param array $current_path
   *
   * @return array
   */
  private function recurseModelData($model_data, &$paths, $current_path = []) {
    if (is_object($model_data) || is_array($model_data)) {
      foreach ($model_data as $key => $data) {
        $new_current_path_path = $current_path;
        $new_current_path_path[] = $key;
        $this->recurseModelData($data, $paths, $new_current_path_path);
      }
    }
    elseif (is_string($model_data) && preg_match('/(^\{{2}.*?[:a-zA-Z0-9_-].*?\}{2}$)|(^\[(?!.*media-reference).*\]$)/', $model_data)) {
      $paths[] = implode('.', $current_path);
    }

    return $paths;

  }

}
