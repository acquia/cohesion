<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\InlineElementHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;

/**
 * Test for inline element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\InlineElementHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\InlineElementHandler
 */
#[CoversMethod(\Drupal\sitestudio_data_transformers\Handlers\ElementLevel\InlineElementHandler::class, 'getData')]
#[Group('Cohesion')]
#[CoversClass(\Drupal\sitestudio_data_transformers\Handlers\ElementLevel\InlineElementHandler::class)]
class InlineElementHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"inline-element","title":"Inline element","status":{"collapsed":false},"uuid":"80690a72-e5c8-4094-a847-c0a8f07d7adb","parentUid":"root","children":[]}],"mapper":{"80690a72-e5c8-4094-a847-c0a8f07d7adb":{"settings":{"formDefinition":[{"formKey":"inline-element-settings","children":[{"formKey":"inline-element-text","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"inline-element-markup","breakpoints":[],"activeFields":[{"name":"htmlMarkup","active":true}]},{"formKey":"inline-element-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"80690a72-e5c8-4094-a847-c0a8f07d7adb":{"settings":{"title":"Inline element","customStyle":[{"customStyle":""}],"htmlMarkup":"span","content":"my text"}}},"previewModel":{"80690a72-e5c8-4094-a847-c0a8f07d7adb":{}},"variableFields":{"80690a72-e5c8-4094-a847-c0a8f07d7adb":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new InlineElementHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\InlineElementHandler::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'inline-element',
      'id' => '80690a72-e5c8-4094-a847-c0a8f07d7adb',
      'data' => [
        'title' => 'Inline element',
        'htmlMarkup' => 'span',
        'content' => 'my text',
      ],
    ];
    parent::testGetData();
  }


}
