<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update component required field.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0041",
 * )
 */
class _0041EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        if (property_exists($json_values, 'model')) {
          foreach ($json_values->model as $uuid => $data) {
            // Check that the "required" property exists under schema.
            if (is_object($data) && property_exists($data, 'settings') &&
              is_object($data->settings) &&
              property_exists($data->settings, 'schema') &&
              property_exists($data->settings->schema, 'required')) {
              // Move "required" property under "settings"
              $json_values->model->{$uuid}->settings->required = $json_values->model->{$uuid}->settings->schema->required;
              // Remove "required" property from "settings -> schema"
              unset($json_values->model->{$uuid}->settings->schema->required);
            }
          }
        }

        $entity->setJsonValue(json_encode($json_values));
      }
    }

    return TRUE;
  }

}
