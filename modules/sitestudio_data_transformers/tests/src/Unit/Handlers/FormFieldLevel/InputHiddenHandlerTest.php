<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler;

/**
 * Test for hidden input form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\InputHiddenHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler
 */
class InputHiddenHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_input_hidden","type":"component","title":"Input hidden","enabled":true,"category":"category-10","componentId":"cpt_input_hidden","uuid":"30cde999-60d4-4dd3-a9bb-933444a846f2","parentUid":"root","status":{},"children":[]}],"mapper":{},"model":{"30cde999-60d4-4dd3-a9bb-933444a846f2":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":"Hidden value"}},"previewModel":{},"variableFields":{},"meta":{}}';
  const COMPONENT = [
    'uuid' => '9d38eabc-f66f-4aa3-ae0a-b1bf26d96859',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Input hidden',
    'id' => 'cpt_input_hidden',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-input-hidden","title":"Hidden input","translate":true,"status":{"collapsed":false},"uuid":"db6a56eb-9f72-49eb-8cce-14edcbd728ac","parentUid":"root","children":[]}],"mapper":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":{}},"model":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":{"settings":{"type":"cohHidden","title":"Hidden input","schema":{"type":"string","escape":true},"machineName":"hidden-input"},"model":{"value":"Hidden value"}}},"previewModel":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":{}},"variableFields":{"db6a56eb-9f72-49eb-8cce-14edcbd728ac":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new InputHiddenHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHiddenHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'db6a56eb-9f72-49eb-8cce-14edcbd728ac',
      'data' => [
        'uid' => 'form-input-hidden',
        'title' => 'Hidden input',
        'value' => 'Hidden value',
      ],
      'machine_name' => 'hidden-input',
    ];
    parent::testGetData();
  }


}
