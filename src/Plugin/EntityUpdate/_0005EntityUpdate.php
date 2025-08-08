<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_elements\CustomElementPluginInterface;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update WYSIWYG elements.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0005",
 * )
 */
class _0005EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
        foreach ($layoutCanvas->iterateModels('component_form') as $model) {
          // If the component form element is a WYSIWYG and has a value update
          // to the new model.
          if ($model->getProperty(['settings', 'type']) == 'cohWysiwyg') {

            if (is_string($model->getProperty(['model', 'value']))) {
              $json_values->model->{$model->getUUID()}->model->value = [
                'text' => $model->getProperty(['model', 'value']),
                'textFormat' => 'cohesion',
              ];
            }

            if ($model->getProperty(['settings', 'schema', 'type']) == 'string') {
              $json_values->model->{$model->getUUID()}->settings->schema->type = 'object';
            }

          }
        }

        // Update the canvas model for WYSIWYG elements, Google map marker
        // elements and WYSIWYG component values.
        foreach ($layoutCanvas->iterateModels('canvas') as $model) {
          // If the element is a WYSIWYG element and the value exists and is
          // not a token or field (start with [ and ends with ])
          if ($model->getElement()->getProperty('uid') == 'wysiwyg' && is_string($model->getProperty([
            'settings',
            'content',
          ])) && !preg_match('/^\[[\s\S]*?\]$/', $model->getProperty([
            'settings',
            'content',
          ]))) {
            $json_values->model->{$model->getUUID()}->settings->content = [
              'text' => $model->getProperty(['settings', 'content']),
              'textFormat' => 'cohesion',
            ];
          }

          // If the element is a Google map marker and the value exists and is
          // not a token or field (start with [ and ends with ])
          if ($model->getElement()->getProperty('uid') == 'google-map-marker' && is_string($model->getProperty([
            'settings',
            'markerInfo',
          ])) && !preg_match('/^\[[\s\S]*?\]$/', $model->getProperty([
            'settings',
            'markerInfo',
          ]))) {
            $json_values->model->{$model->getUUID()}->settings->markerInfo = [
              'text' => $model->getProperty(['settings', 'markerInfo']),
              'textFormat' => 'cohesion',
            ];
          }

          // If the element is a component holding a wysiwyg field and the value
          // exists and is not a token or field (start with [ and ends with ])
          if ($componentId = $model->getElement()->getComponentID()) {
            /** @var \Drupal\cohesion_elements\Entity\Component $component */
            if ($component = $this->loadComponent($componentId)) {
              $component_layout_canvas = $component->getLayoutCanvasInstance();
              foreach ($component_layout_canvas->iterateModels('component_form') as $form_model) {
                if ($form_model->getProperty([
                  'settings',
                  'type',
                ]) == 'cohWysiwyg' && is_string($model->getProperty($form_model->getUUID())) && !preg_match('/^\[[\s\S]*?\]$/', $model->getProperty($form_model->getUUID()))) {
                  $json_values->model->{$model->getUUID()}->{$form_model->getUUID()} = [
                    'text' => $model->getProperty($form_model->getUUID()),
                    'textFormat' => 'cohesion',
                  ];
                }
              }
            }
          }

          // If the element is a custom element holding a wysiwyg field and the
          // value exists and is not a token or field
          // (start with [ and ends with ])
          if ($model->getElement()->getProperty('isCustom') == TRUE && $model->getElement()->getProperty('uid')) {

            if ($fields = $this->getCustomElementFields($model->getElement()->getProperty('uid'))) {
              foreach ($fields as $field_id => $field) {
                if (isset($field['type']) && $field['type'] == 'wysiwyg' && is_string($model->getProperty([
                  'settings',
                  $field_id,
                ])) && !preg_match('/^\[[\s\S]*?\]$/', $model->getProperty([
                  'settings',
                  $field_id,
                ]))) {
                  $json_values->model->{$model->getUUID()}->settings->{$field_id} = [
                    'text' => $model->getProperty(['settings', $field_id]),
                    'textFormat' => 'cohesion',
                  ];
                }
              }
            }
          }
        }
      }
      else {
        if (is_object($json_values) && property_exists($json_values, 'preview') && is_string($json_values->preview)) {
          $json_values->preview = [
            'text' => $json_values->preview,
            'textFormat' => 'cohesion',
          ];
        }
      }

      $entity->setJsonValue(json_encode($json_values));
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
