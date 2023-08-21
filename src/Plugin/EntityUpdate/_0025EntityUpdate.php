<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update tree model.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0025",
 * )
 */
class _0025EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

      // Update Custom styles / Base styles / Style helpers.
      if (!$entity->isLayoutCanvas()) {
        $json_mapper = $entity->getDecodedJsonMapper();
        if (property_exists($json_values, 'styles')) {
          // Process the styles values in the json values.
          $new_css_children = $this->processStyleLevel($json_values->styles, []);
          foreach ($new_css_children as $new_css_child) {
            $json_values->styles->{$new_css_child['uuid']} = $new_css_child['child'];
          }

          if (is_object($json_mapper)) {
            $this->processMapper($json_mapper, $new_css_children);
          }

        }
        $entity->setJsonMapper(json_encode($json_mapper));
      }
      else {
        $layout_canvas = $entity->getLayoutCanvasInstance();

        foreach ($layout_canvas->iterateModels('canvas') as $model) {
          if (property_exists($json_values->model->{$model->getUUID()}, 'styles')) {
            // Process the styles values in the json values.
            $new_css_children = $this->processStyleLevel($json_values->model->{$model->getUUID()}->styles, []);
            foreach ($new_css_children as $new_css_child) {
              $json_values->model->{$model->getUUID()}->styles->{$new_css_child['uuid']} = $new_css_child['child'];
            }

            if (property_exists($json_values, 'mapper') && property_exists($json_values->mapper, $model->getUUID()) && is_object($json_values->mapper->{$model->getUUID()})) {
              $this->processMapper($json_values->mapper->{$model->getUUID()}, $new_css_children);
            }

            if (property_exists($json_values, 'previewModel') && is_object($json_values->previewModel) && property_exists($json_values->previewModel, $model->getUUID()) && is_object($json_values->previewModel->{$model->getUUID()}) && property_exists($json_values->previewModel->{$model->getUUID()}, 'styles')) {
              $preview_model_css_children = $this->processStyleLevel($json_values->previewModel->{$model->getUUID()}->styles, [], $new_css_children);

              foreach ($preview_model_css_children as $preview_model_css_child) {
                $json_values->previewModel->{$model->getUUID()}->styles->{$preview_model_css_child['uuid']} = $preview_model_css_child['child'];
              }

            }

            if (property_exists($json_values, 'variableFields') && is_object($json_values->variableFields) && property_exists($json_values->variableFields, $model->getUUID()) && is_array($json_values->variableFields->{$model->getUUID()})) {
              foreach ($json_values->variableFields->{$model->getUUID()} as &$variableField) {
                foreach ($new_css_children as $new_child_key => $new_css_child) {
                  if (strpos($variableField, 'styles.' . $new_child_key . '.styles') === 0) {
                    $variableField = str_replace('styles.' . $new_child_key . '.styles', 'styles.' . $new_css_child['uuid'] . '.styles', $variableField);
                  }
                }
              }
            }

          }
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   *
   */
  public function generateUUID() {
    return \Drupal::service('uuid')->generate();
  }

  /**
   *
   */
  private function processStyleLevel(&$level_json, $level_key, $generated_css_children = NULL) {

    $css_children = ['children', 'pseudos', 'modifiers', 'prefix'];
    $new_css_children = [];
    if (is_object($level_json)) {
      // Loop over each styles "child".
      foreach ($css_children as $css_child) {

        $css_child_level_key = $level_key;
        $css_child_level_key[] = $css_child;

        // If the property exists and has some values
        // Retain the path "$child_level_key" so it can be used for the mapper
        // and store the values and a newly generated uuid
        // Recursively process the values.
        if (property_exists($level_json, $css_child) && is_array($level_json->{$css_child}) && !empty($level_json->{$css_child})) {
          $child_level = 0;
          foreach ($level_json->{$css_child} as &$child) {
            $child_level_key = $css_child_level_key;
            $child_level_key[] = $child_level;
            $new_css_children = array_merge($new_css_children, $this->processStyleLevel($child, $child_level_key, $generated_css_children));

            $path_to_child = implode('.', $child_level_key);
            if (!is_null($generated_css_children) && isset($generated_css_children[$path_to_child])) {
              $uuid = $generated_css_children[$path_to_child]['uuid'];
            }
            else {
              $uuid = $this->generateUUID();
            }

            $new_css_children[$path_to_child] = [
              'uuid' => $uuid,
              'child' => $child,
            ];
            $child_level++;
          }
        }

        unset($level_json->{$css_child});
      }
    }

    return $new_css_children;
  }

  /**
   *
   */
  private function processMapper(&$json_mapper, $new_css_children) {
    if (property_exists($json_mapper, 'styles')) {
      $this->processMapperLevel($json_mapper->styles, $new_css_children);
    }

    // Foreach property in the mapper, move everything in topLevel
    // one level up and remove topLevel.
    foreach ($json_mapper as &$mapper_prop) {
      if (property_exists($mapper_prop, 'topLevel') && is_object($mapper_prop->topLevel)) {
        foreach ($mapper_prop->topLevel as $property => $value) {
          $mapper_prop->{$property} = $value;
        }
        unset($mapper_prop->topLevel);
      }

      if (property_exists($mapper_prop, 'dropzone')) {
        unset($mapper_prop->dropzone);
      }
    }
  }

  /**
   *
   */
  private function processMapperLevel(&$level_json, $new_css_children) {

    if (property_exists($level_json, 'dropzone')) {
      $level_json->items = $level_json->dropzone;
      unset($level_json->dropzone);
    }

    if (property_exists($level_json, 'items') && is_array($level_json->items)) {
      foreach ($level_json->items as &$item) {

        if (property_exists($item, 'prevKey')) {

          if (isset($new_css_children[implode('.', $item->prevKey)])) {
            $item->uuid = $new_css_children[implode('.', $item->prevKey)]['uuid'];
          }

          unset($item->prevKey);
        }

        if (property_exists($item, 'allowedTypes')) {
          unset($item->allowedTypes);
        }

        if (property_exists($item, 'form')) {
          unset($item->form);
        }

        if (property_exists($item, 'oldModelKey')) {
          unset($item->oldModelKey);
        }

        $this->processMapperLevel($item, $new_css_children);

      }
    }

  }

}
