<?php

namespace Drupal\cohesion;

use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\tmgmt_content\DefaultFieldProcessor;

/**
 * Field processor for the link field.
 */
class CohesionLayoutFieldProcessor extends DefaultFieldProcessor {

  /**
   * {@inheritdoc}
   */
  public function extractTranslatableData(FieldItemListInterface $field) {
    $data = parent::extractTranslatableData($field);

    if ($field->getEntity() instanceof CohesionLayout && $field->getName() == 'json_values' && isset($data[0]['value']['#text'])) {
      $data_layout = [];
      $layout_canvas = new LayoutCanvas($data[0]['value']['#text']);
      foreach ($layout_canvas->iterateModels() as $element_uuid => $model) {
        foreach ($model->getValues() as $key => $value) {
          if (preg_match(ElementModel::MATCH_UUID, $key)) {
            if(is_string($value) && !empty($value)) {
              $data_layout[] = [
                '#text' => $value,
                '#translate' => TRUE,
                '#model_key' => [$element_uuid, $key],
              ];
            }elseif(is_object($value) && property_exists($value, 'text') && property_exists($value, 'textFormat')) {
              $data_layout[] = [
                '#text' => Html::escape($value->text),
                '#translate' => TRUE,
                '#model_key' => [$element_uuid, $key],
              ];
            }
          }
        }
      }

      unset($data[0]);

      $data = array_merge($data, $data_layout);
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function setTranslations($field_data, FieldItemListInterface $field) {
    $data = parent::extractTranslatableData($field);

    if ($field->getEntity() instanceof CohesionLayout && $field->getName() == 'json_values' && isset($data[0]['value']['#text'])) {
      $layout_canvas = new LayoutCanvas($data[0]['value']['#text']);
      foreach ($layout_canvas->iterateModels() as $element_uuid => &$model) {
        foreach ($model->getValues() as $key => $value) {
          foreach ($field_data as $component_field) {
            if (is_array($component_field) && isset($component_field['#model_key']) && $component_field['#model_key'] == [
                $element_uuid,
                $key,
              ]) {

              if(is_string($value) && !empty($value)) {
                $model->setProperty($key, $component_field['#translation']['#text']);
              }elseif(is_object($value) && property_exists($value, 'text') && property_exists($value, 'textFormat')) {
                $new_value = $value;
                $new_value->text = Html::decodeEntities($component_field['#translation']['#text']);
                $model->setProperty($key, $new_value);
              }

            }
          }
        }
      }

      $field->offsetGet(0)->set('value', json_encode($layout_canvas));
    }

  }

}
