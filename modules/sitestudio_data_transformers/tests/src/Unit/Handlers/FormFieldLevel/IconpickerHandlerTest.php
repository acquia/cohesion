<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\IconpickerHandler;

/**
 * Test for icon picker form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\IconpickerHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\IconpickerHandler
 */
class IconpickerHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_iconpicker_component","type":"component","title":"Iconpicker","enabled":true,"category":"category-10","componentId":"cpt_iconpicker_component","uuid":"162972ae-37f6-465b-98fa-2a3eeb8fcbcf","parentUid":"root","status":{},"children":[]}],"mapper":{"162972ae-37f6-465b-98fa-2a3eeb8fcbcf":{}},"model":{"162972ae-37f6-465b-98fa-2a3eeb8fcbcf":{"ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e":{"iconName":61445,"fontFamily":"icomoon"},"settings":{"title":"Icon picker field"}}},"previewModel":{"162972ae-37f6-465b-98fa-2a3eeb8fcbcf":{}},"variableFields":{"162972ae-37f6-465b-98fa-2a3eeb8fcbcf":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'e3b4bfed-e7db-4252-a3af-d38addaea4f9',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Iconpicker component',
    'id' => 'cpt_iconpicker_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-iconpicker","title":"Icon picker","status":{"collapsed":false},"uuid":"ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e","parentUid":"root","children":[]}],"mapper":{"ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e":{}},"model":{"ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e":{"settings":{"type":"cohIconPickerOpener","title":"Icon picker","menuPicker":true,"schema":{"type":"object"},"machineName":"icon-picker","tooltipPlacement":"auto right"}}},"previewModel":{"ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e":{}},"variableFields":{"ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new IconpickerHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\IconpickerHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'ac4bab4f-3178-4d1a-b15d-8c1f5e171a6e',
      'data' => [
        'uid' => 'form-iconpicker',
        'title' => 'Icon picker',
        'value' => json_decode('{"iconName": 61445, "fontFamily": "icomoon"}'),
      ],
      'machine_name' => 'icon-picker',
    ];
    parent::testGetData();
  }

}
