<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\EntityBrowserHandler;

/**
 * Test for entity browser form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\EntityBrowserHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\EntityBrowserHandler
 */
class EntityBrowserHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_entity_browser_test","type":"component","title":"Entity Browser Test","enabled":true,"category":"category-15","componentId":"cpt_entity_browser_test","uuid":"3c831b81-82b9-4127-9f1c-adf772183fa0","parentUid":"root","status":{},"children":[]}],"mapper":{"3c831b81-82b9-4127-9f1c-adf772183fa0":{}},"model":{"3c831b81-82b9-4127-9f1c-adf772183fa0":{"c2dff2a4-33d0-48ca-871d-3ec0e827b008":{"entity":{"entityType":"media","entityUUID":"83039db8-e48a-43fd-b010-e0e2d5b324af","entityId":"83039db8-e48a-43fd-b010-e0e2d5b324af"},"settings":{"options":{"entityBrowserType":"media_library","entityType":"media"}}},"settings":{"title":"Entity Browser field"}}},"previewModel":{"3c831b81-82b9-4127-9f1c-adf772183fa0":{}},"variableFields":{"3c831b81-82b9-4127-9f1c-adf772183fa0":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '093ae4cd-06a3-4599-a77e-cd4af2bf79b1',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Entity Browser Test',
    'id' => 'cpt_entity_browser_test',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-entity-browser","title":"Entity browser","status":{"collapsed":false},"uuid":"c2dff2a4-33d0-48ca-871d-3ec0e827b008","parentUid":"root","children":[]}],"mapper":{"c2dff2a4-33d0-48ca-871d-3ec0e827b008":{}},"model":{"c2dff2a4-33d0-48ca-871d-3ec0e827b008":{"settings":{"title":"Entity browser","type":"cohEntityBrowser","showConfig":false,"hideRowHeading":true,"schema":{"type":"object"},"options":{"entityBrowserType":"media_library","entityBrowserTypeDisabled":true,"entityType":"media","entityTypeDisabled":true,"entityBrowserBundlesDisabled":true},"machineName":"entity-browser","entityBrowserTypeTooltipPlacement":"auto right","entityTypeTooltipPlacement":"auto right","entityBrowserTooltipPlacement":"auto right"},"model":{"value":{"entity":""}}}},"previewModel":{"c2dff2a4-33d0-48ca-871d-3ec0e827b008":{}},"variableFields":{"c2dff2a4-33d0-48ca-871d-3ec0e827b008":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const ENTITY = [
    "entity_type" => "media",
    "entity" => "83039db8-e48a-43fd-b010-e0e2d5b324af",
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->handler = new EntityBrowserHandler(
      $this->moduleHandler,
      $this->getUrlGeneratorMock(),
      $this->getEntityTypeManagerMock(),
      $this->getResourceTypeManagerMock()
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\EntityBrowserHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => 'c2dff2a4-33d0-48ca-871d-3ec0e827b008',
      'data' => [
        'uid' => 'form-entity-browser',
        'title' => 'Entity browser',
        'value' => json_decode('{"entity":{"entityType":"media","entityUUID":"83039db8-e48a-43fd-b010-e0e2d5b324af","entityId":"83039db8-e48a-43fd-b010-e0e2d5b324af"},"settings":{"options":{"entityBrowserType":"media_library","entityType":"media"}},"jsonapi_link":"jsonapi.media.individual/83039db8-e48a-43fd-b010-e0e2d5b324af"}'),
      ],
      'machine_name' => 'entity-browser',
    ];
    parent::testGetData();
  }


}
