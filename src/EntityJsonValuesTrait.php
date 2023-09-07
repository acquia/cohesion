<?php

namespace Drupal\cohesion;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Serialization\Json;

/**
 * Trait for EntityJsonValuesTrait.
 */
trait EntityJsonValuesTrait {

  /**
   * Gets the api processor manager.
   *
   * @return ApiPluginManager
   */
  public function apiProcessorManager() {
    return \Drupal::service('plugin.manager.api.processor');
  }

  /**
   * {@inheritdoc}
   */
  public function getDecodedJsonValues($as_object = FALSE) {
    try {
      if ($as_object) {
        return json_decode($this->getJsonValues());
      }
      else {
        return Json::decode($this->getJsonValues());
      }
    }
    catch (\Exception $e) {
      return [];
    }
  }

  /**
   * @inheritdoc
   */
  public function getLayoutCanvasInstance() {
    if ($this->isLayoutCanvas()) {
      return new LayoutCanvas($this->getJsonValues());
    }

    return FALSE;
  }

  /**
   * @return mixed
   */
  public function preProcessJsonValues() {
    $this->resetElementsUUIDs();
    if ($canvas_instance = $this->getLayoutCanvasInstance()) {
      $this->setJsonValue(json_encode($canvas_instance));
    }
    return FALSE;
  }

  /**
   * Validate that values sumbmited for components on a layout canvas are legal.
   *
   * @return bool|array
   *
   *   Returns FALS if no error or an array representing the error
   */
  public function validateComponentValues() {

    $layout_canvas = $this->getLayoutCanvasInstance();

    $original_json = NULL;
    if (!$this->isNew()) {
      $original_json = $this->load($this->id())->getDecodedJsonValues();
    }

    foreach ($layout_canvas->iterateCanvas() as $element) {
      if ($element->isComponent() && $element->getModel()) {
        if ($component = Component::load($element->getProperty('componentId'))) {
          $component_json_values = $component->getDecodedJsonValues();
          foreach ($element->getModel()
            ->getValues() as $field_uuid => $model_value) {
            if (isset($component_json_values['model'][$field_uuid]['settings']) && !is_null($model_value)) {
              $field_model_settings = $component_json_values['model'][$field_uuid]['settings'];

              // Make the type of the input field matches the type in the value.
              if ((!isset($field_model_settings['type']) && (!isset($field_model_settings['schema']['type']) || $field_model_settings['schema']['type'] == 'number' && !is_numeric($model_value) || $field_model_settings['schema']['type'] == 'string' && !is_string($model_value))) || (isset($field_model_settings['type']) && $field_model_settings['type'] == 'checkboxToggle' && !is_bool($model_value))) {
                return [
                  'uuid' => $element->getUUID(),
                  'error' => t('Illegal value in %component_title for field: %field_name', [
                    '%component_title' => $element->getProperty('title'),
                    '%field_name' => $field_model_settings['title'],
                  ]),
                ];
              }

              // Make sure user has the right to use the text format defined in
              // the WYSIWYG componet field.
              if (isset($field_model_settings['type']) && $field_model_settings['type'] == 'cohWysiwyg' && is_object($model_value) && property_exists($model_value, 'textFormat') && property_exists($model_value, 'text')) {

                $user_formats = filter_formats(\Drupal::currentUser());
                $all_formats = filter_formats();

                if (array_key_exists($model_value->textFormat, $all_formats)) {
                  // If the user doesn't have persmission on this text format.
                  if (!array_key_exists($model_value->textFormat, $user_formats)) {
                    // If new entity return en error.
                    if ($this->isNew()) {
                      return [
                        'uuid' => $element->getUUID(),
                        'error' => t("You don't have the right permission to use the text format %text_format in %component_title for field: %field_name", [
                          '%text_format' => $all_formats[$model_value->textFormat]->label(),
                          '%component_title' => $element->getProperty('title'),
                          '%field_name' => $field_model_settings['title'],
                        ]),
                      ];
                    }
                    else {
                      // Only return an error if the text format or the content
                      // has changed.
                      if (!isset($original_json['model'][$element->getUUID()][$field_uuid]) ||
                        !isset($original_json['model'][$element->getUUID()][$field_uuid]['text']) ||
                        $original_json['model'][$element->getUUID()][$field_uuid]['text'] != $model_value->text ||
                        !isset($original_json['model'][$element->getUUID()][$field_uuid]['textFormat']) ||
                        $original_json['model'][$element->getUUID()][$field_uuid]['textFormat'] != $model_value->textFormat) {
                        return [
                          'uuid' => $element->getUUID(),
                          'error' => t("You don't have the right permission to use the text format %text_format in %component_title for field: %field_name", [
                            '%text_format' => $all_formats[$model_value->textFormat]->label(),
                            '%component_title' => $element->getProperty('title'),
                            '%field_name' => $field_model_settings['title'],
                          ]),
                        ];
                      }
                    }
                  }
                }
                else {
                  return [
                    'uuid' => $element->getUUID(),
                    'error' => t('Illegal value in %component_title for field: %field_name, the text format does not exist', [
                      '%component_title' => $element->getProperty('title'),
                      '%field_name' => $field_model_settings['title'],
                    ]),
                  ];
                }
              }
            }
          }
        }
      }
    }

    return FALSE;
  }

  /**
   * Reset UUID in a layout canvas
   * This prevent Helpers or duplicate of a entity to share the same UUIDs as
   * another entity.
   */
  public function resetElementsUUIDs() {
    $is_modified = method_exists($this, 'isModified') ? $this->isModified() : FALSE;

    // The !isModified() makes sure that imported entities are ignored.
    if (($this->isNew() && !$is_modified) && $this->getJsonValues()) {
      $json_values = $this->getDecodedJsonValues(TRUE);

      if (property_exists($json_values, 'canvas')) {
        // Replace uuids in layout canvas elements and its attached model and
        // mapper.
        $this->resetLayoutCanvasElementUUIDS($json_values->canvas, $json_values);

      }
      $new_json_values = Json::encode($json_values);

      if (property_exists($json_values, 'componentForm')) {
        // Replace uuids in component form elements and its attached model and
        // mapper.
        $replacement_uuids = $this->resetLayoutCanvasElementUUIDS($json_values->componentForm, $json_values);
        // Replace reference to component form fields in layout canvas.
        $new_json_values = str_replace($replacement_uuids['old'], $replacement_uuids['new'], $new_json_values);
      }

      $this->setJsonValue($new_json_values);
    }
  }

  /**
   * Replace uuids in a layout canvas.
   *
   * @param $object
   *   array|object A reference to a node of a layout canvas
   * @param $json_values
   *   array A reference to the entire json values
   *
   * @return array
   */
  private function resetLayoutCanvasElementUUIDS(&$object, &$json_values) {
    $replacement_uuids = [
      'old' => [],
      'new' => [],
    ];
    if (is_object($object)) {

      if (property_exists($object, 'children') && is_array($object->children) && !empty($object->children)) {
        foreach ($object->children as &$child) {
          array_merge($replacement_uuids, $this->resetLayoutCanvasElementUUIDS($child, $json_values));
        }
      }

      if (property_exists($object, 'uuid')) {
        $old_uuid = $object->uuid;
        $new_uuid = \Drupal::service('uuid')->generate();
        $replacement_uuids['old'][] = $old_uuid;
        $replacement_uuids['new'][] = $new_uuid;

        $object->uuid = $new_uuid;
        $this->replaceModelMapperUUIDS($json_values, 'model', $old_uuid, $new_uuid);
        $this->replaceModelMapperUUIDS($json_values, 'previewModel', $old_uuid, $new_uuid);
        $this->replaceModelMapperUUIDS($json_values, 'variableFields', $old_uuid, $new_uuid);
        $this->replaceModelMapperUUIDS($json_values, 'mapper', $old_uuid, $new_uuid);
      }
    }
    elseif (is_array($object)) {
      foreach ($object as $element) {
        array_merge($replacement_uuids, $this->resetLayoutCanvasElementUUIDS($element, $json_values));
      }
    }
    return $replacement_uuids;
  }

  /**
   * Replace uuids in model or mapper in json values.
   *
   * @param &$json_values
   *   array  A reference the entire json values
   * @param $type
   *   string  Model or mapper
   * @param $old_uuid
   *   string The old uuid to replace
   * @param $new_uuid
   *   string The new uuid to replace
   */
  private function replaceModelMapperUUIDS(&$json_values, $type, $old_uuid, $new_uuid) {
    if (property_exists($json_values, $type) && is_object($json_values->$type) && property_exists($json_values->$type, $old_uuid) && $old_uuid !== $new_uuid) {
      $json_values->$type->$new_uuid = $json_values->$type->$old_uuid;
      unset($json_values->$type->$old_uuid);
    }
  }

}
