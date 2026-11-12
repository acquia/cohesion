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
 *   id = "entityupdate_0038",
 * )
 */
class _0038EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    $removeHtmlClass = ['cohAccordion', 'cohArray'];
    if (in_array($model->getProperty(['settings', 'type']), $removeHtmlClass)) {
      $model->unsetProperty(['settings', 'htmlClass']);
    }

    $sectionTab = ['cohSection', 'cohTabItem'];
    if (in_array($model->getProperty(['settings', 'type']), $sectionTab)) {
      if ($columnCount = $model->getProperty(['settings', 'columnCount'])) {
        if (preg_match('/^coh-component-field-group-?([1-4]?)-col$/', $columnCount, $matchesColumnCount)) {
          if ($matchesColumnCount[1] == '') {
            $model->setProperty(['settings', 'columnCount'], 'auto');
          } else {
            $model->setProperty(['settings', 'columnCount'], $matchesColumnCount[1]);
          }
        }
      }

      $breakpointIcon = $model->getProperty(['settings', 'breakpointIcon']);
      if ($breakpointIcon !== '' && preg_match('/^coh-breakpoint-icon coh-icon-(television|desktop|laptop|tablet|mobile-landscape|mobile)$/', $breakpointIcon, $matchesBreakpointIcon)) {
        $model->setProperty(['settings', 'breakpointIcon'], $matchesBreakpointIcon[1]);
      }

    }
  }

}
