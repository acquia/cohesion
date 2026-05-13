<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Migrate hideData to hideDataFields for Hide if no data feature.
 *
 * @EntityUpdate(
 *   id = "entityupdate_0044",
 *   requireAPI = FALSE,
 * )
 */
class _0044EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if (!($entity instanceof EntityJsonValuesInterface) || !$entity->isLayoutCanvas()) {
      return TRUE;
    }

    $json_values = $entity->getDecodedJsonValues(TRUE);

    if (!property_exists($json_values, 'model')) {
      return TRUE;
    }

    foreach ($json_values->model as &$element) {
      if (is_object($element)) {
        $this->processHideNoData($element);
      }
    }

    $entity->setJsonValue(json_encode($json_values));

    return TRUE;
  }

  /**
   * Migrate legacy hideData scalar into hideDataFields array.
   */
  private function processHideNoData(object &$element): bool {
    if (!property_exists($element, 'hideNoData')) {
      return FALSE;
    }

    $hide_no_data = &$element->hideNoData;

    if (!is_object($hide_no_data) || !property_exists($hide_no_data, 'hideData')) {
      return FALSE;
    }

    if (!is_string($hide_no_data->hideData) && !is_numeric($hide_no_data->hideData)) {
      return FALSE;
    }

    $value = trim((string) $hide_no_data->hideData);
    unset($hide_no_data->hideData);

    if ($value === '') {
      return TRUE;
    }

    if (!property_exists($hide_no_data, 'hideDataFields') || !is_array($hide_no_data->hideDataFields)) {
      $hide_no_data->hideDataFields = [];
    }

    if (!$this->hasHideDataField($hide_no_data->hideDataFields, $value)) {
      array_unshift($hide_no_data->hideDataFields, (object) ['hideDataField' => $value]);
    }

    return TRUE;
  }

  /**
   * Check if hideDataFields already contains the given value.
   */
  private function hasHideDataField(array $fields, string $value): bool {
    foreach ($fields as $field) {
      if (is_object($field) && property_exists($field, 'hideDataField') && $field->hideDataField === $value) {
        return TRUE;
      }
      if (is_array($field) && isset($field['hideDataField']) && $field['hideDataField'] === $value) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
