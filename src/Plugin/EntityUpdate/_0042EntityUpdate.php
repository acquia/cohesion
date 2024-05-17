<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update validationMessage format for form elements on components.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0042",
 * )
 */
class _0042EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        $layoutCanvas = $entity->getLayoutCanvasInstance();
        $json_values = $entity->getDecodedJsonValues(TRUE);

        // Update Component form elements.
        if (property_exists($json_values, 'componentForm')) {
          foreach ($layoutCanvas->iterateComponentForm() as $element) {
            if ($element->getModel()) {
              $this->updateFormElement($element->getModel());
            }
          }
        }

        $entity->setJsonValue(json_encode($layoutCanvas));
      }
    }

    return TRUE;
  }

  /**
   * Update validationMessage on form element.
   *
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $model
   */
  private function updateFormElement(ElementModel $model) {
    $validationMessage = $model->getProperty(['settings', 'validationMessage']);
    if (is_array($validationMessage) && isset($validationMessage[302]) && is_string($validationMessage[302])) {
      $validationMessage['required'] = $validationMessage[302];
      unset($validationMessage[302]);
      foreach ($validationMessage as $key => $message) {
        if (is_null($message)) {
          unset($validationMessage[$key]);
        }
      }
      $model->setProperty(['settings', 'validationMessage'], $validationMessage);
    }
  }

}
