<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\EntityBrowserHandler;

/**
 * Test for entity browser element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\EntityBrowserHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\EntityBrowserHandler
 */
class EntityBrowserHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"entity-browser","title":"Entity browser","status":{"collapsed":true},"uuid":"532c21f5-b2ad-484a-b5ee-9922c54bff15","parentUid":"root","children":[]}],"mapper":{"532c21f5-b2ad-484a-b5ee-9922c54bff15":{"settings":{"formDefinition":[{"formKey":"entity-browser-settings","children":[{"formKey":"entity-browser","breakpoints":[],"activeFields":[]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"532c21f5-b2ad-484a-b5ee-9922c54bff15":{"settings":{"title":"Entity browser","options":{"entityBrowserType":"media_library","entityType":"media"},"entity":{"entity":{"entityType":"media","entityUUID":"8f296ba9-cc77-4f49-b178-3587b21156e8","entityId":"8f296ba9-cc77-4f49-b178-3587b21156e8"}},"entityViewMode":"media.default"}}},"previewModel":{"532c21f5-b2ad-484a-b5ee-9922c54bff15":{}},"variableFields":{"532c21f5-b2ad-484a-b5ee-9922c54bff15":[]},"meta":{}}';

  const ENTITY = [
    "entity_type" => "media",
    "view_mode" => "full",
    "entity" => "8f296ba9-cc77-4f49-b178-3587b21156e8",
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
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\EntityBrowser::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'entity-browser',
      'id' => '532c21f5-b2ad-484a-b5ee-9922c54bff15',
      'data' => [
        'title' => 'Entity browser',
        'options' => json_decode('{"entityBrowserType":"media_library", "entityType":"media"}'),
        'value' => [
          'entity' => json_decode('{"entityType":"media","entityUUID":"8f296ba9-cc77-4f49-b178-3587b21156e8","entityId":"8f296ba9-cc77-4f49-b178-3587b21156e8"}'),
          'jsonapi_link' => 'jsonapi.media.individual/8f296ba9-cc77-4f49-b178-3587b21156e8'
        ],
      ],
    ];
    parent::testGetData();
  }


}
