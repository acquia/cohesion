<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_elements\CustomElementPluginInterface;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update picture elements.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0007",
 * )
 */
class _0007EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

        // Update the canvas model for WYSIWYG elements, Google map marker
        // elements and WYSIWYG component values.
        foreach ($layoutCanvas->iterateModels('canvas') as $model) {
          $element_property = $model->getProperty(
            [
              'styles',
              'settings',
              'element',
            ]
          );
          $styles_property = $model->getProperty(['settings', 'styles']);
          if ($element_property == 'picture' && is_object($styles_property)) {
            foreach ($model->getProperty(['settings', 'styles']) as $breakpoint => $style) {
              if (!property_exists($style, 'pictureImagesArray')) {
                $pictureImagesArray = [];

                if (property_exists($style, 'image')) {
                  $pictureImagesArray['image'] = $style->image;
                  unset($json_values->model->{$model->getUUID()}->settings->styles->{$breakpoint}->image);
                }

                if (property_exists($style, 'imageStyle')) {
                  $pictureImagesArray['imageStyle'] = $style->imageStyle;
                  unset($json_values->model->{$model->getUUID()}->settings->styles->{$breakpoint}->imageStyle);
                }

                $json_values->model->{$model->getUUID()}->settings->styles->{$breakpoint}->pictureImagesArray = [$pictureImagesArray];
              }
            }

          }
        }

        $entity->setJsonValue(json_encode($json_values));
      }

    }

    return TRUE;
  }

  /**
   *
   */
  public function loadComponent($componentId) {
    return Component::load($componentId);
  }

  /**
   *
   */
  public function getCustomElementFields($uid) {
    $custom_element_plugin_manager = \Drupal::service('plugin.manager.custom_elements');
    /** @var \Drupal\cohesion_elements\CustomElementPluginInterface $instance */
    if ($instance = $custom_element_plugin_manager->createInstance($uid)) {
      if ($instance instanceof CustomElementPluginInterface) {
        return $instance->getFields();
      }
    }
    return FALSE;
  }

}
