<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\BlockquoteHandler;

/**
 * Test for blockquote element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\BlockquoteHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\BlockquoteHandler
 */
class BlockquoteHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"blockquote","title":"Block quote","status":{"collapsed":true},"uuid":"04d51c7e-d10d-4513-815b-ba3762c8a80f","parentUid":"root","children":[]}],"mapper":{"04d51c7e-d10d-4513-815b-ba3762c8a80f":{"settings":{"formDefinition":[{"formKey":"blockquote-settings","children":[{"formKey":"blockquote-block-quote","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"blockquote-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"04d51c7e-d10d-4513-815b-ba3762c8a80f":{"settings":{"title":"Block quote","customStyle":[{"customStyle":""}],"content":"my block quote text"}}},"previewModel":{"04d51c7e-d10d-4513-815b-ba3762c8a80f":{}},"variableFields":{"04d51c7e-d10d-4513-815b-ba3762c8a80f":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new BlockquoteHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Blockquote::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'blockquote',
      'id' => '04d51c7e-d10d-4513-815b-ba3762c8a80f',
      'data' => [
        'title' => 'Block quote',
        'content' => 'my block quote text',
      ],
    ];
    parent::testGetData();
  }


}
