<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_elements\Entity\CohesionElementEntityBase;
use Drupal\Component\Plugin\PluginBase;

/**
 * Change dropzone settings.label to settings.title.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0009",
 * )
 */
class _0009EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof CohesionElementEntityBase) {

      $json_values = $entity->getDecodedJsonValues(TRUE);

      if (property_exists($json_values, 'model')) {
        foreach ($json_values->model as $uuid => $data) {
          // If it's a dropzone entry.
          if (is_object($data) && property_exists($data, 'settings') && is_object($data->settings) && property_exists($data->settings, 'dropzoneHideSelector') && property_exists($data->settings, 'label')) {
            // Convert the 'label' key to 'title'.
            $json_values->model->{$uuid}->settings->title = $json_values->model->{$uuid}->settings->label;
            unset($json_values->model->{$uuid}->settings->label);
          }
        }
      }

      // Save the data back out.
      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

}
