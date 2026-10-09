<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler;

/**
 * Test for wysiwyg form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\WysiwygHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler
 */
class WysiwygHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_wysiwyg_component","type":"component","title":"Wysiwyg component","enabled":true,"category":"category-10","componentId":"cpt_wysiwyg_component","uuid":"690192aa-a9e1-425c-9faa-f9acb17420be","parentUid":"root","status":{},"children":[]}],"mapper":{"690192aa-a9e1-425c-9faa-f9acb17420be":{}},"model":{"690192aa-a9e1-425c-9faa-f9acb17420be":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{"textFormat":"cohesion","text":"<p>A very long string value in WYSIWYG editor.<\/p>"},"settings":{"title":"WYSIWYG"}}},"previewModel":{"690192aa-a9e1-425c-9faa-f9acb17420be":{}},"variableFields":{"690192aa-a9e1-425c-9faa-f9acb17420be":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '2c1e2da5-658d-4447-a48e-29b0274ef8d7',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Wysiwyg component',
    'id' => 'cpt_wysiwyg_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-wysiwyg","title":"WYSIWYG","translate":true,"status":{"collapsed":false},"uuid":"fd4282cf-10c9-47b9-afa8-5422944b00b7","parentUid":"root","children":[]}],"mapper":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{}},"model":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{"settings":{"type":"cohWysiwyg","title":"WYSIWYG","schema":{"type":"object"},"machineName":"wysiwyg","tooltipPlacement":"auto right"},"model":{"value":{"textFormat":"cohesion","text":""}}}},"previewModel":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":{}},"variableFields":{"fd4282cf-10c9-47b9-afa8-5422944b00b7":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new WysiwygHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\WysiwygHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'fd4282cf-10c9-47b9-afa8-5422944b00b7',
      'data' => [
        'uid' => 'form-wysiwyg',
        'title' => 'WYSIWYG',
        'value' => json_decode('{"textFormat": "cohesion", "text": "<p>A very long string value in WYSIWYG editor.</p>"}'),
      ],
      'machine_name' => 'wysiwyg',
    ];

    parent::testGetData();
  }


}
