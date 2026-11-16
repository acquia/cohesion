<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\EntityReferenceHandler;

/**
 * Test for entity reference element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\EntityReferenceHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\EntityReferenceHandler
 */
class EntityReferenceHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"entity-reference","title":"Entity reference","status":{"collapsed":true},"uuid":"2155f5a8-a020-4964-885c-e668ce30f9ad","parentUid":"root","children":[]}],"mapper":{"2155f5a8-a020-4964-885c-e668ce30f9ad":{"settings":{"formDefinition":[{"formKey":"entity-reference-settings","children":[{"formKey":"entity-reference","breakpoints":[],"activeFields":[{"name":"entity_type","active":true},{"name":"view_mode","active":true},{"name":"entity","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"2155f5a8-a020-4964-885c-e668ce30f9ad":{"settings":{"title":"Entity reference","entityReference":{"entity_type":"node","view_mode":"default","entity":"18486638-15d9-43c3-8e4e-2d28ca825b32"}}}},"previewModel":{"2155f5a8-a020-4964-885c-e668ce30f9ad":{}},"variableFields":{"2155f5a8-a020-4964-885c-e668ce30f9ad":[]},"meta":{}}';

  const ENTITY = [
    "entity_type" => "node",
    "view_mode" => "full",
    "entity" => "18486638-15d9-43c3-8e4e-2d28ca825b32",
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
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\EntityReference::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'entity-reference',
      'id' => '2155f5a8-a020-4964-885c-e668ce30f9ad',
      'data' => [
        'title' => 'Entity reference',
        'value' => json_decode('{"entity_type": "node", "view_mode": "default", "entity": "18486638-15d9-43c3-8e4e-2d28ca825b32", "jsonapi_link":"jsonapi.node.individual/18486638-15d9-43c3-8e4e-2d28ca825b32"}'),
      ],
    ];
    parent::testGetData();
  }


}
