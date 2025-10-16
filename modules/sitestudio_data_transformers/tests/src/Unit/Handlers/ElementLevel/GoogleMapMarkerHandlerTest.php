<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\GoogleMapMarkerHandler;

/**
 * Test for google map marker element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\GoogleMapMarkerHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\GoogleMapMarkerHandler
 */
class GoogleMapMarkerHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"item","uid":"google-map-marker","title":"Google map marker","status":{"collapsed":true},"uuid":"1b2ac999-e927-4cca-bc59-e003077597eb","parentUid":"root","children":[]}],"mapper":{"1b2ac999-e927-4cca-bc59-e003077597eb":{"settings":{"formDefinition":[{"formKey":"google-map-marker-settings","children":[{"formKey":"google-map-marker-label","breakpoints":[],"activeFields":[{"name":"label","active":true}]},{"formKey":"google-map-marker-location","breakpoints":[],"activeFields":[{"name":"latlong","active":true}]},{"formKey":"google-map-marker-marker","breakpoints":[],"activeFields":[{"name":"markerType","active":true},{"name":"googleMarker","active":true}]},{"formKey":"google-map-marker-link","breakpoints":[],"activeFields":[{"name":"type","active":true},{"name":"linkToPage","active":true},{"name":"url","active":true},{"name":"anchor","active":true}]},{"formKey":"google-map-marker-info-window","breakpoints":[],"activeFields":[{"name":"markerInfo","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"1b2ac999-e927-4cca-bc59-e003077597eb":{"settings":{"markerType":"default","link":{"type":"none"},"title":"Google map marker","markerInfo":{"textFormat":"basic_html","text":"<p>marker info text<\/p>"},"infoWindowClass":"","label":"marker label"}}},"previewModel":{"1b2ac999-e927-4cca-bc59-e003077597eb":{}},"variableFields":{"1b2ac999-e927-4cca-bc59-e003077597eb":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new GoogleMapMarkerHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\GoogleMapMarker::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'google-map-marker',
      'id' => '1b2ac999-e927-4cca-bc59-e003077597eb',
      'data' => [
        'title' => 'Google map marker',
        'link' => [
          'type' => 'none',
        ],
        'markerInfo' => [
          'text' => '<p>marker info text</p>',
          'textFormat' => 'basic_html',
        ],
        'label' => 'marker label',
        'markerType' => 'default',
      ],
    ];
    parent::testGetData();
  }


}
