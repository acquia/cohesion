<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ParagraphHandler;

/**
 * Test for paragraph element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\ParagraphHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ParagraphHandler
 */
class ParagraphHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"paragraph","title":"Paragraph","status":{"collapsed":true},"uuid":"2a9b2f71-31c8-41c9-adcc-ac6336928527","parentUid":"root","children":[]}],"mapper":{"2a9b2f71-31c8-41c9-adcc-ac6336928527":{"settings":{"formDefinition":[{"formKey":"paragraph-settings","children":[{"formKey":"paragraph-paragraph","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"paragraph-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"2a9b2f71-31c8-41c9-adcc-ac6336928527":{"settings":{"title":"Paragraph","customStyle":[{"customStyle":""}],"content":"test paragraph text"}}},"previewModel":{"2a9b2f71-31c8-41c9-adcc-ac6336928527":{}},"variableFields":{"2a9b2f71-31c8-41c9-adcc-ac6336928527":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new ParagraphHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Paragraph::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'paragraph',
      'id' => '2a9b2f71-31c8-41c9-adcc-ac6336928527',
      'data' => [
        'title' => 'Paragraph',
        'content' => 'test paragraph text',
      ],
    ];
    parent::testGetData();
  }


}
