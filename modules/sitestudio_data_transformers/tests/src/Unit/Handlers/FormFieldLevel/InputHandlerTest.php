<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler;

/**
 * Test for input form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\InputHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler
 */
class InputHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_input_test","type":"component","title":"Input test","enabled":true,"category":"category-1","componentId":"cpt_input_test","uuid":"ac783511-11fa-4611-9c0b-5ffee389c3f1","parentUid":"root","status":{},"children":[]}],"mapper":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":{}},"model":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":{"2758c170-b86d-4c7c-95f2-71db785ff827":"The Test Input","settings":{"title":"Input"}}},"previewModel":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":{}},"variableFields":{"ac783511-11fa-4611-9c0b-5ffee389c3f1":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '25afa449-dc79-4cca-a5fc-f7f7e441c3fd',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Input component',
    'id' => 'cpt_input_test',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-input","title":"Input","translate":true,"status":{"collapsed":false},"uuid":"2758c170-b86d-4c7c-95f2-71db785ff827","parentUid":"root","children":[]}],"mapper":{"2758c170-b86d-4c7c-95f2-71db785ff827":{}},"model":{"2758c170-b86d-4c7c-95f2-71db785ff827":{"settings":{"type":"cohTextBox","title":"Input","schema":{"type":"string","escape":true},"machineName":"input","tooltipPlacement":"auto right"}}},"previewModel":{"2758c170-b86d-4c7c-95f2-71db785ff827":{}},"variableFields":{"2758c170-b86d-4c7c-95f2-71db785ff827":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new InputHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\InputHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '2758c170-b86d-4c7c-95f2-71db785ff827',
      'data' => [
        'uid' => 'form-input',
        'title' => 'Input',
        'value' => 'The Test Input',
      ],
      'machine_name' => 'input',
    ];
    parent::testGetData();
  }


}
