<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler;

/**
 * Test for link form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\LinkHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler
 */
class LinkHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_link_component","type":"component","title":"Link component","enabled":true,"category":"category-10","componentId":"cpt_link_component","uuid":"de6add66-d75d-4453-9e6a-cee240ab9a34","parentUid":"root","status":{},"children":[]}],"mapper":{"de6add66-d75d-4453-9e6a-cee240ab9a34":{}},"model":{"de6add66-d75d-4453-9e6a-cee240ab9a34":{"settings":{"title":"Link component"},"5149b61c-8fd2-49d1-9127-713dc1683576":"node::1"}},"previewModel":{"de6add66-d75d-4453-9e6a-cee240ab9a34":{}},"variableFields":{"de6add66-d75d-4453-9e6a-cee240ab9a34":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => 'dfa3f357-c977-4644-a98d-27719086abc2',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Link component',
    'id' => 'cpt_link_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-link","title":"Link to page","translate":true,"status":{"collapsed":false},"uuid":"5149b61c-8fd2-49d1-9127-713dc1683576","parentUid":"root","children":[]}],"mapper":{"5149b61c-8fd2-49d1-9127-713dc1683576":{}},"model":{"5149b61c-8fd2-49d1-9127-713dc1683576":{"settings":{"isStyle":true,"type":"cohTypeahead","key":"linkToPage","title":"Link to page","placeholder":"Type page name","labelProperty":"name","valueProperty":"id","typeaheadEditable":true,"endpoint":"\/cohesionapi\/link-autocomplete?q=","schema":{"type":"string"},"machineName":"link-to-page","tooltipPlacement":"auto right","entityTypes":false}}},"previewModel":{"5149b61c-8fd2-49d1-9127-713dc1683576":{}},"variableFields":{"5149b61c-8fd2-49d1-9127-713dc1683576":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new LinkHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\LinkHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '5149b61c-8fd2-49d1-9127-713dc1683576',
      'data' => [
        'uid' => 'form-link',
        'title' => 'Link to page',
        'value' => 'node::1',
      ],
      'machine_name' => 'link-to-page',
    ];
    parent::testGetData();
  }


}
