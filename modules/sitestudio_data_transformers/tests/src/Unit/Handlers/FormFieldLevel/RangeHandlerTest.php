<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler;

/**
 * Test for range form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\RangeHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler
 */
class RangeHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_range_component","type":"component","title":"Range component","enabled":true,"category":"category-10","componentId":"cpt_range_component","uuid":"647c5bc4-c689-4b95-b4be-7862248fda9f","parentUid":"root","status":{},"children":[]}],"mapper":{"647c5bc4-c689-4b95-b4be-7862248fda9f":{}},"model":{"647c5bc4-c689-4b95-b4be-7862248fda9f":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":7,"settings":{"title":"Range slider"}}},"previewModel":{"647c5bc4-c689-4b95-b4be-7862248fda9f":{}},"variableFields":{"647c5bc4-c689-4b95-b4be-7862248fda9f":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '819f52be-463d-4b6d-8e46-7a1e4d0a902a',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Range component',
    'id' => 'cpt_range_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-range-slider","title":"Range slider","status":{"collapsed":false},"uuid":"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3","parentUid":"root","children":[]}],"mapper":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":{}},"model":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":{"settings":{"type":"cohRange","title":"Range slider","min":0,"max":10,"step":1,"schema":{"type":"number"},"machineName":"range-slider","tooltipPlacement":"auto right"}}},"previewModel":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":{}},"variableFields":{"7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new RangeHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\RangeHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '7bb62938-77e5-4ca5-b4e6-ab4a749ee7b3',
      'data' => [
        'uid' => 'form-range-slider',
        'title' => 'Range slider',
        'value' => 7,
      ],
      'machine_name' => 'range-slider',
    ];
    parent::testGetData();
  }


}
