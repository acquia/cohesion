<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler;

/**
 * Test for textarea form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\TextareaHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler
 */
class TextareaHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_textarea_component","type":"component","title":"Textarea component","enabled":true,"category":"category-10","componentId":"cpt_textarea_component","uuid":"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae","parentUid":"root","status":{},"children":[]}],"mapper":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":{}},"model":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":"Some plain text.","settings":{"title":"Plain text area"}}},"previewModel":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":{}},"variableFields":{"e9ece08f-2c9c-45b0-b761-e8bf83aaf9ae":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '2768d34f-87aa-482f-b26c-084f8a873b92',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Textarea component',
    'id' => 'cpt_textarea_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-textarea","title":"Plain text area","translate":true,"status":{"collapsed":false},"uuid":"b2c96013-a09c-46b8-a0f9-bb18afd827b4","parentUid":"root","children":[]}],"mapper":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":{}},"model":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":{"settings":{"type":"cohTextarea","title":"Plain text area","schema":{"type":"string","escape":true},"machineName":"plain-text-area","tooltipPlacement":"auto right"}}},"previewModel":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":{}},"variableFields":{"b2c96013-a09c-46b8-a0f9-bb18afd827b4":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new TextareaHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\TextareaHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'b2c96013-a09c-46b8-a0f9-bb18afd827b4',
      'data' => [
        'uid' => 'form-textarea',
        'title' => 'Plain text area',
        'value' => 'Some plain text.',
      ],
      'machine_name' => 'plain-text-area',
    ];
    parent::testGetData();
  }


}
