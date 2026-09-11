<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\DrupalBlockHandler;

/**
 * Test for drupal block element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\DrupalBlockHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\DrupalBlockHandler
 */
class DrupalBlockHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"drupal-block","title":"Block","status":{"collapsed":true},"uuid":"248685be-4ca3-4259-b293-ae418d5d11b4","parentUid":"root","children":[]}],"mapper":{"248685be-4ca3-4259-b293-ae418d5d11b4":{"settings":{"formDefinition":[{"formKey":"block-settings","children":[{"formKey":"block-drupal-block","breakpoints":[],"activeFields":[{"name":"block","active":true}]},{"formKey":"block-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"248685be-4ca3-4259-b293-ae418d5d11b4":{"settings":{"title":"Block","customStyle":[{"customStyle":""}],"theme":"cohesion_theme","block":"cohesion_theme_powered"}}},"previewModel":{"248685be-4ca3-4259-b293-ae418d5d11b4":{}},"variableFields":{"248685be-4ca3-4259-b293-ae418d5d11b4":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new DrupalBlockHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\DrupalBlock::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'drupal-block',
      'id' => '248685be-4ca3-4259-b293-ae418d5d11b4',
      'data' => [
        'title' => 'Block',
        'theme' => 'cohesion_theme',
        'block' => 'cohesion_theme_powered',
      ],
    ];
    parent::testGetData();
  }


}
