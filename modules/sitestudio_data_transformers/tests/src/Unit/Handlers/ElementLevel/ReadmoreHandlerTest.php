<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ReadMoreHandler;

/**
 * Test for read more element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\ReadMoreHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ReadMoreHandler
 */
class ReadMoreHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"read-more","title":"Read more","status":{"collapsed":false},"uuid":"d9cddd71-9461-456c-a745-9b8f93134766","parentUid":"root","children":[]}],"mapper":{"d9cddd71-9461-456c-a745-9b8f93134766":{"settings":{"formDefinition":[{"formKey":"read-more-settings","children":[{"formKey":"read-more-button","breakpoints":[],"activeFields":[{"name":"buttonTextCollapsed","active":true},{"name":"buttonStyleCollapsed","active":true},{"name":"buttonTextExpanded","active":true},{"name":"buttonStyleExpanded","active":true}]},{"formKey":"read-more-button-animation","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"animationType","active":true}]},{"formKey":"read-more-initial-state","breakpoints":[{"name":"xl"}],"activeFields":[{"name":"visibility","active":true}]},{"formKey":"read-more-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"d9cddd71-9461-456c-a745-9b8f93134766":{"settings":{"title":"Read more","customStyle":[{"customStyle":""}],"buttonStyle":"","styles":{"xl":{"animationType":"none","visibility":"hidden"}},"buttonTextCollapsed":"button text col","buttonTextExpanded":"button text expanded"}}},"previewModel":{"d9cddd71-9461-456c-a745-9b8f93134766":{}},"variableFields":{"d9cddd71-9461-456c-a745-9b8f93134766":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new ReadMoreHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ReadMore::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'read-more',
      'id' => 'd9cddd71-9461-456c-a745-9b8f93134766',
      'data' => [
        'title' => 'Read more',
      ],
    ];
    parent::testGetData();
  }


}
