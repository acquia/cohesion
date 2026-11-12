<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler;

/**
 * Test for select form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\SelectHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler
 */
class SelectHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_select_component","type":"component","title":"Select component","enabled":true,"category":"category-10","componentId":"cpt_select_component","uuid":"548667b3-513b-4007-89d3-42a44369465c","parentUid":"root","status":{},"children":[]}],"mapper":{"548667b3-513b-4007-89d3-42a44369465c":{}},"model":{"548667b3-513b-4007-89d3-42a44369465c":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":"three","settings":{"title":"Select"}}},"previewModel":{"548667b3-513b-4007-89d3-42a44369465c":{}},"variableFields":{"548667b3-513b-4007-89d3-42a44369465c":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'ee1bb744-a89a-47f7-ab25-f15d7048a44d',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Select component',
    'id' => 'cpt_select_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-select","title":"Select","translate":false,"status":{"collapsed":false},"uuid":"20cb3bae-243d-4fcb-b355-0b40b31180a3","parentUid":"root","children":[]}],"mapper":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":{}},"model":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":{"settings":{"type":"cohSelect","title":"Select","selectType":"custom","machineName":"select","options":[{"label":"One","value":"one"},{"label":"Two","value":"two"},{"label":"Three","value":"three"}],"tooltipPlacement":"auto right"},"model":{"value":""}}},"previewModel":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":{}},"variableFields":{"20cb3bae-243d-4fcb-b355-0b40b31180a3":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new SelectHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\SelectHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '20cb3bae-243d-4fcb-b355-0b40b31180a3',
      'data' => [
        'uid' => 'form-select',
        'title' => 'Select',
        'value' => 'three',
      ],
      'machine_name' => 'select',
    ];
    parent::testGetData();
  }


}
