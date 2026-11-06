<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\HeadingHandler;

/**
 * Test for heading element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\HeadingHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\HeadingHandler
 */
class HeadingHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"heading","title":"Heading","status":{"collapsed":true},"iconColor":"content","uuid":"1d1963a6-33a7-4843-bb66-f5acf32f6ac2","parentUid":"root","children":[]}],"mapper":{"1d1963a6-33a7-4843-bb66-f5acf32f6ac2":{"settings":{"formDefinition":[{"formKey":"heading-settings","children":[{"formKey":"heading-element","breakpoints":[],"activeFields":[{"name":"element","active":true}]},{"formKey":"heading-heading","breakpoints":[],"activeFields":[{"name":"content","active":true}]},{"formKey":"heading-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true},{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"1d1963a6-33a7-4843-bb66-f5acf32f6ac2":{"settings":{"element":"h1","title":"Heading","customStyle":[{"customStyle":""}],"content":"my test heading"}}},"previewModel":{"1d1963a6-33a7-4843-bb66-f5acf32f6ac2":{}},"variableFields":{"1d1963a6-33a7-4843-bb66-f5acf32f6ac2":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new HeadingHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Heading::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'heading',
      'id' => '1d1963a6-33a7-4843-bb66-f5acf32f6ac2',
      'data' => [
        'title' => 'Heading',
        'content' => 'my test heading',
      ],
    ];
    parent::testGetData();
  }


}
