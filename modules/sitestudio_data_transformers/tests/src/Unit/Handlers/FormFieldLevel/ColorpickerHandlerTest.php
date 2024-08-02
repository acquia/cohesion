<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler;

/**
 * Test for color picker form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\ColorpickerHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler
 */
class ColorpickerHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_colorpicker_component","type":"component","title":"Colorpicker component","enabled":true,"category":"category-10","componentId":"cpt_colorpicker_component","uuid":"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6","parentUid":"root","status":{},"children":[]}],"mapper":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":{}},"model":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":{"ed299839-006b-4e38-a368-d4e0c674ca38":{"value":{"hex":"#3899ec","rgba":"rgba(56, 153, 236, 1)"}},"settings":{"title":"Color picker field"}}},"previewModel":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":{}},"variableFields":{"51d6bf75-9905-44b4-bbc7-2edfa9f7f1e6":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'ed299839-006b-4e38-a368-d4e0c674ca38',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Colorpicker component',
    'id' => 'cpt_colorpicker_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-colorpicker","title":"Color picker","status":{"collapsed":false},"uuid":"ed299839-006b-4e38-a368-d4e0c674ca38","parentUid":"root","children":[]}],"mapper":{"ed299839-006b-4e38-a368-d4e0c674ca38":{}},"model":{"ed299839-006b-4e38-a368-d4e0c674ca38":{"settings":{"type":"cohColourPickerOpener","title":"Color picker","colourPickerOptions":{"flat":true,"showOnly":""},"schema":{"type":"object"},"machineName":"color-picker","restrictBy":"none","tooltipPlacement":"auto right"}}},"previewModel":{"ed299839-006b-4e38-a368-d4e0c674ca38":{}},"variableFields":{"ed299839-006b-4e38-a368-d4e0c674ca38":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
   protected function setUp(): void {
    parent::setUp();
    $this->handler = new ColorpickerHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ColorpickerHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'ed299839-006b-4e38-a368-d4e0c674ca38',
      'data' => [
        'uid' => 'form-colorpicker',
        'title' => 'Color picker',
        'value' => json_decode('{"value": {"hex": "#3899ec", "rgba": "rgba(56, 153, 236, 1)"}}'),
      ],
      'machine_name' => 'color-picker',
    ];
    parent::testGetData();
  }


}
