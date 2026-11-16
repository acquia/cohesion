<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_style_guide\Entity\StyleGuideManager;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update toggles in style guide managers.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0019",
 * )
 */
class _0019EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity && \Drupal::service('module_handler')->moduleExists('cohesion_style_guide') && $entity instanceof StyleGuideManager) {
      /** @var \Drupal\cohesion_style_guide\Entity\StyleGuideManager $entity */

      if ($entity->get('style_guide_uuid')) {

        $style_guide_uuid = $entity->get('style_guide_uuid');

        $this->process($entity, $style_guide_uuid);
      }

    }

    return TRUE;
  }

  /**
   *
   */
  public function styleGuideLoad($style_guide_uuid) {
    return \Drupal::service('entity.repository')->loadEntityByUuid('cohesion_style_guide', $style_guide_uuid);
  }

  /**
   *
   */
  public function process(&$entity, $style_guide_uuid) {
    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuide $style_guide */
    if ($style_guide = $this->styleGuideLoad($style_guide_uuid)) {
      $json_values = $entity->getDecodedJsonValues(TRUE);

      if (property_exists($json_values, 'model') && property_exists($json_values->model, $style_guide_uuid)) {
        $layout_canvas = $style_guide->getLayoutCanvasInstance();
        foreach ($layout_canvas->iterateStyleGuideForm() as $form_element) {
          if ($form_element->getModel()->getProperty(['settings', 'type']) == 'checkboxToggle' &&
            property_exists($json_values->model->{$style_guide_uuid}, $form_element->getUUID()) &&
            !is_bool($json_values->model->{$style_guide_uuid}->{$form_element->getUUID()})) {

            $true_value = $form_element->getModel()->getProperty(
              [
                'settings',
                'trueValue',
              ]
            );

            if ($json_values->model->{$style_guide_uuid}->{$form_element->getUUID()} == $true_value) {
              $json_values->model->{$style_guide_uuid}->{$form_element->getUUID()} = TRUE;
            }
            else {
              $json_values->model->{$style_guide_uuid}->{$form_element->getUUID()} = FALSE;
            }

          }
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }
  }

}
