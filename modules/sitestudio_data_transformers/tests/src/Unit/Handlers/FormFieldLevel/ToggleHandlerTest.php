<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler;

/**
 * Test for toggle form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\ToggleHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler
 */
class ToggleHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_toggle_component","type":"component","title":"Toggle component","enabled":true,"category":"category-10","componentId":"cpt_toggle_component","uuid":"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b","parentUid":"root","status":{},"children":[]}],"mapper":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":{}},"model":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":true,"settings":{"title":"Toggle"}}},"previewModel":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":{}},"variableFields":{"a794d5b5-b3e8-41ba-b8f2-5ccf96fead1b":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'c71cc59f-6b63-446b-b068-c0394682a24b',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Toggle component',
    'id' => 'cpt_toggle_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-checkbox-toggle","title":"Toggle","translate":true,"status":{"collapsed":false},"uuid":"9a30a966-497e-49e2-8a3b-28b1f493ee8b","parentUid":"root","children":[]}],"mapper":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":{}},"model":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":{"settings":{"type":"checkboxToggle","title":"Toggle","schema":{"type":"string"},"machineName":"toggle","toggleType":"boolean","tooltipPlacement":"auto right"},"model":{"value":false}}},"previewModel":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":{}},"variableFields":{"9a30a966-497e-49e2-8a3b-28b1f493ee8b":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new ToggleHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\ToggleHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '9a30a966-497e-49e2-8a3b-28b1f493ee8b',
      'data' => [
        'uid' => 'form-checkbox-toggle',
        'title' => 'Toggle',
        'value' => true,
      ],
      'machine_name' => 'toggle',
    ];
    parent::testGetData();
  }


}
