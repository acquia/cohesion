<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\IframeHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test for iframe element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\IframeHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\IframeHandler
 */
#[CoversMethod(\Drupal\sitestudio_data_transformers\Handlers\ElementLevel\IframeHandler::class, 'getData')]
#[Group('Cohesion')]
#[CoversClass(\Drupal\sitestudio_data_transformers\Handlers\ElementLevel\IframeHandler::class)]
class IframeHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"iframe","title":"iFrame","status":{"collapsed":true},"uuid":"39b15d4a-c6c7-4a0d-9b53-87a4845bddc9","parentUid":"root","children":[]}],"mapper":{"39b15d4a-c6c7-4a0d-9b53-87a4845bddc9":{"settings":{"formDefinition":[{"formKey":"iframe-settings","children":[{"formKey":"iframe-src","breakpoints":[],"activeFields":[{"name":"src","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"39b15d4a-c6c7-4a0d-9b53-87a4845bddc9":{"settings":{"title":"iFrame","src":"https:\/\/www.google.com"}}},"previewModel":{"39b15d4a-c6c7-4a0d-9b53-87a4845bddc9":{}},"variableFields":{"39b15d4a-c6c7-4a0d-9b53-87a4845bddc9":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new IframeHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\IframeHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'iframe',
      'id' => '39b15d4a-c6c7-4a0d-9b53-87a4845bddc9',
      'data' => [
        'title' => 'iFrame',
        'src' => 'https://www.google.com',
      ],
    ];
    parent::testGetData();
  }


}
