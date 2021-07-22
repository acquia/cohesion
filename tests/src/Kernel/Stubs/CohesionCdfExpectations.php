<?php

namespace Drupal\Tests\cohesion\Kernel\Stubs;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\Tests\acquia_contenthub\Kernel\Stubs\CdfExpectations;

/**
 * Class CdfExpectations.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\acquia_contenthub\Kernel\Stubs
 */
class CohesionCdfExpectations extends CdfExpectations {

  /**
   * Return field value.
   *
   * @inheritDoc
   */
  public function getFieldValue(string $field_name, string $langcode = NULL) {

    $field_value = parent::getFieldValue($field_name, $langcode);

    if ($field_name == 'json_values' && !empty($field_value) && isset($field_value[0]['value'])) {
      $layout_canvas = new LayoutCanvas($field_value[0]['value']);
      $field_value[0]['value'] = json_encode($layout_canvas);
    }

    return $field_value;
  }

}
