<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update help text elements to new value storage.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0020",
 * )
 */
class _0020EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
          $this->updateHelpText($model, $json_values);
        }

        // Update component field default values.
        foreach ($layoutCanvas->iterateModels('style_guide_form') as $model) {
          $this->updateHelpText($model, $json_values);
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
  private function updateHelpText($model, &$json_values) {
    $type_property = $model->getProperty(['settings', 'type']);
    $value_property = $model->getProperty(['model', 'value']);
    if ($type_property == 'cohHelpText' && $value_property) {
      $value = $model->getProperty(['model', 'value']);
      unset($json_values->model->{$model->getUUID()}->model->value);
      $json_values->model->{$model->getUUID()}->settings->options = new \stdClass();
      $json_values->model->{$model->getUUID()}->settings->options->helpText = $value;
    }
  }

}
