<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\EntityReferenceHandler;

/**
 * Test for entity reference form field handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\FormFieldLevel\EntityReferenceHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\EntityReferenceHandler
 */
class EntityReferenceHandlerTest extends FormFieldHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"uid":"cpt_entity_reference_component","type":"component","title":"Entity reference component","enabled":true,"category":"category-10","componentId":"cpt_entity_reference_component","uuid":"1d65e3f0-3fb1-4500-bc12-1d4517424688","parentUid":"root","status":{},"children":[]}],"mapper":{"1d65e3f0-3fb1-4500-bc12-1d4517424688":{}},"model":{"1d65e3f0-3fb1-4500-bc12-1d4517424688":{"00550314-3843-456e-bf49-c7e06de8a671":{"entity_type":"node","view_mode":"full","entity":"f02bb06a-bff6-4aa2-b303-0d6724f7a6d6"},"settings":{"title":"Entity reference component"}}},"previewModel":{"1d65e3f0-3fb1-4500-bc12-1d4517424688":{}},"variableFields":{"1d65e3f0-3fb1-4500-bc12-1d4517424688":[]},"meta":{}}';
  const COMPONENT = [
    'uuid' => '908f2ce4-ec26-4512-8cd7-f3c4d57a97cf',
    'langcode' => 'en',
    'status' => true,
    'label' => 'Entity reference component',
    'id' => 'cpt_entity_reference_component',
    'json_values' => '{"canvas":[],"componentForm":[{"type":"form-field","uid":"form-entity-reference","title":"Entity reference","selected":false,"status":{"collapsed":false,"isopen":false},"uuid":"00550314-3843-456e-bf49-c7e06de8a671","parentUid":"root","children":[]}],"mapper":{"00550314-3843-456e-bf49-c7e06de8a671":{}},"model":{"00550314-3843-456e-bf49-c7e06de8a671":{"settings":{"title":"Entity reference","type":"cohEntityPicker","schema":{"type":"object"},"machineName":"entity-reference","entityTypeTooltipPlacement":"right","viewModeTooltipPlacement":"right","entityTooltipPlacement":"right","options":{"entity_type_disabled":false}},"model":{"value":{"entity_type":"node"}}}},"previewModel":{"00550314-3843-456e-bf49-c7e06de8a671":{}},"variableFields":{"00550314-3843-456e-bf49-c7e06de8a671":[]},"disabledNodes":[],"meta":{}}',
    'json_mapper' => '{}',
    'category' => 'cpt_cat_test',
  ];
  const ENTITY = [
    "entity_type" => "node",
    "view_mode" => "full",
    "entity" => "f02bb06a-bff6-4aa2-b303-0d6724f7a6d6",
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->handler = new EntityReferenceHandler(
      $this->moduleHandler,
      $this->getUrlGeneratorMock(),
      $this->getEntityTypeManagerMock(),
      $this->getResourceTypeManagerMock()
    );
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\EntityReferenceHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'form-field',
      'id' => '00550314-3843-456e-bf49-c7e06de8a671',
      'data' => [
        'uid' => 'form-entity-reference',
        'title' => 'Entity reference',
        'value' => json_decode('{"entity_type":"node","view_mode":"full","entity":"f02bb06a-bff6-4aa2-b303-0d6724f7a6d6","jsonapi_link":"jsonapi.node.individual/f02bb06a-bff6-4aa2-b303-0d6724f7a6d6"}'),
      ],
      'machine_name' => 'entity-reference',
    ];
    parent::testGetData();
  }


}
