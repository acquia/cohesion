<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update class name for form elements on components
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0037",
 * )
 */
class _0037EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

        // Remove from Component form.
        if (property_exists($json_values, 'componentForm')) {
          foreach ($layoutCanvas->iterateComponentForm() as $element) {
            if ($element->getModel()) {
              $this->updateFormElement($element->getModel());
            }
          }
        }

        // Remove from Style guide form.
        if (property_exists($json_values, 'styleGuideForm')) {
          foreach ($layoutCanvas->iterateStyleGuideForm() as $element) {
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
   * Update classes on form element
   *
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $model
   */
  private function updateFormElement(ElementModel $model) {
    // Remove htmlClass from cohAccordion and cohArray
    if ($model->getProperty(['settings', 'type']) == 'cohHelpText') {
      $helpType = $model->getProperty(['settings', 'options', 'helpType']);
      if(preg_match('/coh-help-text--(help|warning)$/', $helpType, $matchesColumnCount)) {
        $model->setProperty(['settings', 'options', 'helpType'], $matchesColumnCount[1]);
      }
    }
  }

}
