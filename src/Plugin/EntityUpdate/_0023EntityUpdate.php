<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Supports placing breakpoint icons in component forms (ACO-17)
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0023",
 * )
 */
class _0023EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        foreach ($layoutCanvas->iterateComponentForm() as $element) {
          $this->updateResponsiveSettings($element, $json_values);
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   * @param Element $element
   * @param $json_values
   */
  private function updateResponsiveSettings($element, &$json_values) {
    if ($model = $element->getModel()) {

      if ($element->getProperty('uid') === 'form-tab-container') {
        $json_values->model->{$model->getUUID()}->settings->responsiveMode = TRUE;
      }

      $form_uids = ['form-section', 'form-tab-item'];
      if (in_array($element->getProperty('uid'), $form_uids)) {
        $json_values->model->{$model->getUUID()}->settings->breakpointIcon = "";
      }
    }
  }

}
