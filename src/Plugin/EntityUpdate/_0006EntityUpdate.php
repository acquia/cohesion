<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update background images set to none.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0006",
 * )
 */
class _0006EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
          if ($model->getProperty(['styles'])) {
            $json_values->model->{$model->getUUID()}->styles = $this->setBackgroundImagesToNone($model->getProperty(['styles']));
          }
        }
      }
      else {
        if (is_object($json_values) && property_exists($json_values, 'styles')) {
          $json_values->styles = $this->setBackgroundImagesToNone($json_values->styles);
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   *
   */
  private function setBackgroundImagesToNone($styles) {
    if (is_object($styles)) {
      // Loop over all style breakpoints.
      if (property_exists($styles, 'styles') && is_object($styles->styles)) {
        foreach ($styles->styles as $breakpoint => $bp_style) {
          // Is the current breakpoint has some background.
          if (is_object($bp_style) && property_exists($bp_style, 'background-image-settings') && is_array($bp_style->{'background-image-settings'})) {
            // Loop over each background.
            foreach ($bp_style->{'background-image-settings'} as $index => $background_image) {
              // If the background is a background image.
              if (property_exists($background_image, 'backgroundLayerType') && property_exists($background_image->backgroundLayerType, 'value') && $background_image->backgroundLayerType->value == 'image') {
                // Set the background type to none if the background image has
                // no images set.
                if (!property_exists($background_image, 'backgroundImage') || !is_object($background_image->backgroundImage) || !property_exists($background_image->backgroundImage, 'value') || strpos($background_image->backgroundImage->value, '[media-reference') != 0) {
                  $styles->styles->{$breakpoint}->{'background-image-settings'}[$index]->backgroundLayerType->value = 'none';
                  if (property_exists($background_image, 'backgroundImage') && is_object($background_image->backgroundImage)) {
                    unset($styles->styles->{$breakpoint}->{'background-image-settings'}[$index]->backgroundImage);
                  }

                  foreach ($styles->styles->{$breakpoint}->{'background-image-settings'}[$index] as $background_property_name => $background_property) {
                    if ($background_property_name != 'backgroundLayerType') {
                      $styles->styles->{$breakpoint}->{'background-image-settings'}[$index]->{$background_property_name} = new \stdClass();
                    }
                  }
                }
              }
            }
          }
        }
      }

      if (property_exists($styles, 'pseudos') && is_array($styles->pseudos)) {
        foreach ($styles->pseudos as $index => $pseudo) {
          $styles->pseudos[$index] = $this->setBackgroundImagesToNone($pseudo);
        }
      }

      if (property_exists($styles, 'children') && is_array($styles->children)) {
        foreach ($styles->children as $index => $child) {
          $styles->children[$index] = $this->setBackgroundImagesToNone($child);
        }
      }

      if (property_exists($styles, 'modifiers') && is_array($styles->modifiers)) {
        foreach ($styles->modifiers as $index => $modifier) {
          $styles->modifiers[$index] = $this->setBackgroundImagesToNone($modifier);
        }
      }

      if (property_exists($styles, 'prefix') && is_array($styles->prefix)) {
        foreach ($styles->prefix as $index => $prefix) {
          $styles->prefix[$index] = $this->setBackgroundImagesToNone($prefix);
        }
      }
    }

    return $styles;
  }

}
