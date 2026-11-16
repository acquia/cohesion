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
 *   id = "entityupdate_0016",
 * )
 */
class _0016EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

      if (property_exists($json_values, 'model')) {
        foreach ($json_values->model as $uuid => $data) {
          if (is_object($data) && property_exists($data, 'settings') && is_object($data->settings) && property_exists($data->settings, 'allowAll')) {

            if ($json_values->model->{$uuid}->settings->allowAll == TRUE) {
              unset($json_values->model->{$uuid}->settings->allowAll);
              $json_values->model->{$uuid}->settings->restrictBy = "none";
            }
            else {
              unset($json_values->model->{$uuid}->settings->allowAll);
              $json_values->model->{$uuid}->settings->restrictBy = "colors";
            }
          }
        }
      }

      // Save the data back out.
      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

}
