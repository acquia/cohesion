<?php

namespace Drupal\cohesion;

use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\tmgmt_content\DefaultFieldProcessor;

/**
 * Field processor for the Site Studio layout field.
 */
class CohesionLayoutFieldProcessor extends DefaultFieldProcessor {

  /**
   * {@inheritdoc}
   */
  public function extractTranslatableData(FieldItemListInterface $field) {
    $data = parent::extractTranslatableData($field);

    if ($field->getEntity() instanceof CohesionLayout && $field->getName() == 'json_values' && isset($data[0]['value']['#text'])) {
      // Get the layout canvas json from the data and build a LayoutCanvas.
      $layout_canvas = new LayoutCanvas($data[0]['value']['#text']);

      // Loop over each element in the canvas and load each component if the
      // element is one.
      foreach ($layout_canvas->iterateCanvas() as $element) {
        // Is it a component?
        if ($element->isComponent()) {
          $component = Component::load($element->getComponentID());
          // If we can't load the component, is it a custom component?
          if (!isset($component)) {
            if ($element->isCustomComponent()) {
              $customComponent = \Drupal::service('custom.components')->getComponent($element->getComponentID());
              $component = \Drupal::service('custom.components')->formatAsComponent($customComponent);
            }
          }

          // Get the models of each form field of the component as an array
          // keyed by their uuid.
          $component_model = $component->getLayoutCanvasInstance()
            ->iterateModels('component_form');
          if ($element->getModel()) {
            $data = array_merge($data, $this->processValues($element->getModel()
              ->getValues(), $component_model, [$element->getUUID()]));
          }
        }
      }

      unset($data[0]);
    }

    return $data;
  }

  /**
   *
   */
  private function processValues($values, $component_model, $model_key = []) {
    $data_layout = [];
    foreach ($values as $key => $value) {
      // If the key in the model matches a uuid then it a component field value
      // If the model contains (property) and is TRUE, the field is excluded
      // from being expose as translatable.
      if (preg_match(ElementModel::MATCH_UUID, $key)) {
        if (is_array($value)) {
          foreach ($value as $index => $inner_value) {
            $data_layout = array_merge($data_layout, $this->processValues($inner_value, $component_model, array_merge($model_key, [
              $key,
              $index,
            ])));
          }
        }
        elseif (isset($component_model[$key]) && $component_model[$key]->getProperty([
          'settings',
          'translate',
        ]) !== FALSE) {

          $form_elements = \Drupal::keyValue('cohesion.assets.form_elements');
          $field_uid = $component_model[$key]->getElement()->getProperty('uid');
          $form_field = $form_elements->get($field_uid);
          if (isset($form_field['translate']) && $form_field['translate'] === TRUE) {
            // Only expose value that is a string or a WYSIWYG.
            if (is_string($value) && !empty($value)) {
              $data_layout[] = [
                '#text' => $value,
                '#translate' => TRUE,
                '#model_key' => array_merge($model_key, [$key]),
              ];
            }
            elseif (is_object($value) && property_exists($value, 'text') && property_exists($value, 'textFormat')) {
              $data_layout[] = [
                '#text' => $value->text,
                '#translate' => TRUE,
                '#model_key' => array_merge($model_key, [$key]),
                '#format' => $value->textFormat,
              ];
            }
          }
        }
      }
    }
    return $data_layout;
  }

  /**
   * {@inheritdoc}
   */
  public function setTranslations($field_data, FieldItemListInterface $field) {
    $data = parent::extractTranslatableData($field);

    if ($field->getEntity() instanceof CohesionLayout && $field->getName() == 'json_values' && isset($data[0]['value']['#text'])) {
      $layout_canvas = new LayoutCanvas($data[0]['value']['#text']);
      foreach ($layout_canvas->iterateModels() as $element_uuid => &$model) {
        $values = $this->valuesWithModelKey($model->getValues(), [$element_uuid]);

        foreach ($field_data as $component_field) {
          foreach ($values as $value) {
            if (is_array($component_field) && isset($component_field['#model_key']) && $component_field['#model_key'] == $value['model_key']) {
              if (is_string($value['value']) && !empty($value['value'])) {
                $model->setProperty(array_splice($value['model_key'], 1), $component_field['#translation']['#text']);
              }
              elseif (is_object($value['value']) && property_exists($value['value'], 'text') && property_exists($value['value'], 'textFormat')) {
                $new_value = clone $value['value'];
                $new_value->text = Html::decodeEntities($component_field['#translation']['#text']);
                $model->setProperty(array_splice($value['model_key'], 1), $new_value);
              }
            }
          }
        }
      }

      $field->offsetGet(0)->set('value', json_encode($layout_canvas));
    }
    else {
      parent::setTranslations($field_data, $field);
    }

  }

  /**
   *
   */
  private function valuesWithModelKey($values, $keys = []) {
    $model_key_values = [];
    foreach ($values as $uuid => $value) {
      if (is_array($value)) {
        foreach ($value as $key => $inner_values) {
          $model_key_values = array_merge($model_key_values, $this->valuesWithModelKey($inner_values, array_merge($keys, [
            $uuid,
            $key,
          ])));
        }
      }
      else {
        $model_key_values[] = [
          'value' => $value,
          'model_key' => array_merge($keys, [$uuid]),
        ];
      }

    }
    return $model_key_values;
  }

}
