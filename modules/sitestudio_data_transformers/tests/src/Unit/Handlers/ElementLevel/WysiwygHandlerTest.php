<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\WysiwygHandler;

/**
 * Test for wysiwyg element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\WysiwygHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\WysiwygHandler
 */
class WysiwygHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"wysiwyg","title":"WYSIWYG","status":{"collapsed":true},"uuid":"625d4130-5bc5-47d5-acb8-d9f3be8c7c4f","parentUid":"root","children":[]}],"mapper":{"625d4130-5bc5-47d5-acb8-d9f3be8c7c4f":{"settings":{"formDefinition":[{"formKey":"wysiwyg-settings","children":[{"formKey":"wysiwyg-editor","breakpoints":[],"activeFields":[{"name":"content","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"625d4130-5bc5-47d5-acb8-d9f3be8c7c4f":{"settings":{"title":"WYSIWYG","content":{"textFormat":"basic_html","text":"<p>my test wysiwyg text<\/p>"}}}},"previewModel":{"625d4130-5bc5-47d5-acb8-d9f3be8c7c4f":{}},"variableFields":{"625d4130-5bc5-47d5-acb8-d9f3be8c7c4f":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new WysiwygHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Wysiwyg::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'wysiwyg',
      'id' => '625d4130-5bc5-47d5-acb8-d9f3be8c7c4f',
      'data' => [
        'title' => 'WYSIWYG',
        'content' => [
          "text" => "<p>my test wysiwyg text</p>",
          "textFormat" => "basic_html",
        ],
      ],
    ];
    parent::testGetData();
  }


}
