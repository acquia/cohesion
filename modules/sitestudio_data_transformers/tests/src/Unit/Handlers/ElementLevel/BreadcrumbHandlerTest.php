<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\BreadcrumbHandler;

/**
 * Test for breadcrumb element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\BreadcrumbHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\BreadcrumbHandler
 */
class BreadcrumbHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"breadcrumb","title":"Breadcrumb","status":{"collapsed":true},"uuid":"d976d33d-9910-403c-9370-7ab43726b338","parentUid":"root","children":[]}],"mapper":{"d976d33d-9910-403c-9370-7ab43726b338":{"settings":{"formDefinition":[{"formKey":"breadcrumb-settings","children":[{"formKey":"breadcrumb-style","breakpoints":[],"activeFields":[{"name":"customStyle","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"d976d33d-9910-403c-9370-7ab43726b338":{"settings":{"title":"Breadcrumb","customStyle":""}}},"previewModel":{"d976d33d-9910-403c-9370-7ab43726b338":{}},"variableFields":{"d976d33d-9910-403c-9370-7ab43726b338":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new BreadcrumbHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\Breadcrumb::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'breadcrumb',
      'id' => 'd976d33d-9910-403c-9370-7ab43726b338',
      'data' => [
        'title' => 'Breadcrumb',
      ],
    ];
    parent::testGetData();
  }


}
