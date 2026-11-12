<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Clean up old style model data.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0029",
 * )
 */
class _0029EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

    // Update Custom styles / Base styles / Style helpers
    if ($entity instanceof EntityJsonValuesInterface && !$entity->isLayoutCanvas()) {
      $json_values = $entity->getDecodedJsonValues(TRUE);
      $json_mapper = $entity->getDecodedJsonMapper();

      if (is_object($json_mapper) && property_exists($json_values, 'styles')) {
        $mapper_style_uuids = [];
        // Loop over the mapper.
        $this->walkStyleMapper($json_mapper, function ($uuid) use (&$mapper_style_uuids) {
          $mapper_style_uuids[] = $uuid;
        });

        // Check the style mapper uuids against the model style uuids
        $this->processStyleModel($json_values, $mapper_style_uuids);
      }

      // Save the data back out.
      $entity->setJsonValue(json_encode($json_values));

    } elseif ($entity instanceof EntityJsonValuesInterface) {

      $json_values = $entity->getDecodedJsonValues(TRUE);

      // Loop over each element in the canvas.
      if ($json_values->mapper) {
        $mapper_style_uuids = [];
        foreach ($json_values->mapper as $element_uuid => $mapper) {
          // This element has styles.
          if (is_object($mapper) && property_exists($mapper, 'styles')) {
            $this->walkStyleMapper($mapper, function ($uuid) use (&$mapper_style_uuids) {
              $mapper_style_uuids[] = $uuid;
            });
          }
        }

        // Check the style mapper uuids against the model style uuids
        $this->processModel($entity, $json_values, $mapper_style_uuids);
      }

      // Save the data back out.
      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   * Loop through the mapper and find UUIDs to process.
   * @param $items
   * @param $callback
   */
  private function walkStyleMapper($items, $callback) {
    if (is_object($items)) {
      // Loop over all style breakpoints.
      if (property_exists($items, 'styles') && is_object($items->styles)) {
        $items = [$items->styles];
      }
    }

    foreach ($items as $item) {
      // Process the children (recurse).
      if (property_exists($item, 'items') && !empty($item->items)) {
        $this->walkStyleMapper($item->items, $callback);
      }

      // Process the item.
      if (property_exists($item, 'uuid')) {
        $callback($item->uuid);
      }
    }
  }

  /**
   * Check the style mapper uuids in the mapper_style_uuids array against the
   * model style uuids.
   * @param $entity
   * @param $json_values
   * @param $mapper_style_uuids
   */
  private function processModel($entity, $json_values, $mapper_style_uuids) {
    $layoutCanvas = $entity->getLayoutCanvasInstance();

    foreach($layoutCanvas->iterateModels('canvas') as $model) {
      if (property_exists($json_values->model->{$model->getUUID()}, 'styles')) {

        foreach ($json_values->model->{$model->getUUID()}->styles as $uuid => $model_style) {
          if (isset($model_style->styles) && $uuid !== 'styles') {
            // if the uuid doesn't exist in the mapper then unset.
            if(!in_array($uuid, $mapper_style_uuids)) {
              unset($json_values->model->{$model->getUUID()}->styles->$uuid);
            }
          }
        }
      }
    }
  }

  /**
   *  Process style entity model
   * @param $json_values
   * @param $mapper_style_uuids
   */
  private function processStyleModel($json_values, $mapper_style_uuids) {

    if (property_exists($json_values, 'styles')) {
      foreach ($json_values->styles as $uuid => $style) {
        if (isset($style->styles) && $uuid !== 'styles') {
          if(!in_array($uuid, $mapper_style_uuids)) {
            unset($json_values->styles->$uuid);
          }
        }
      }
    }
  }

}
