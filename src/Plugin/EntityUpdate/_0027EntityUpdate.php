<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Using data attributes as CSS selector for prefix.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0027",
 * )
 */
class _0027EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

    // Is an layout entity.
    if($entity instanceof EntityJsonValuesInterface) {

      if (!$entity->isLayoutCanvas()) {
        $json_values = $entity->getDecodedJsonValues(TRUE);
        $this->walkStyleMapper($entity->getDecodedJsonMapper(), function ($uuid) use (&$json_values) {

          if (isset($json_values->styles->{$uuid}->settings->class) && $json_values->styles->{$uuid}->settings->class[0] !== '.') {
            $json_values->styles->{$uuid}->settings->class = '.' . $json_values->styles->{$uuid}->settings->class;
          }
        });
        $entity->setJsonValue(json_encode($json_values));
      }
      else {
        $json_values = $entity->getDecodedJsonValues(TRUE);

        // Loop over each element in the canvas.
        if ($json_values->mapper) {
          foreach ($json_values->mapper as $element_uuid => $mapper) {
            // This element has styles.
            if (is_object($mapper) && property_exists($mapper, 'styles')) {
              $this->walkStyleMapper($mapper, function ($uuid) use ($element_uuid, &$json_values) {
                if (isset($json_values->model->$element_uuid->styles->$uuid->settings->class) && $json_values->model->$element_uuid->styles->$uuid->settings->class[0] !== '.') {
                  $json_values->model->$element_uuid->styles->$uuid->settings->class = '.' . $json_values->model->$element_uuid->styles->$uuid->settings->class;
                }
              });
            }
          }
        }

        $entity->setJsonValue(json_encode($json_values));
      }
    }

    return TRUE;
  }

  /**
   * Loop through the mapper and find UUIDs to process.
   *
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
      if (property_exists($item, 'selectorType') && property_exists($item, 'uuid') && $item->selectorType === 'prefix') {
        $callback($item->uuid);
      }
    }

  }

}
