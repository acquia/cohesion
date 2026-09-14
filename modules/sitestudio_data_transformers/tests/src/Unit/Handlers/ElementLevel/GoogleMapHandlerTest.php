<?php

namespace Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\GoogleMapHandler;

/**
 * Test for google map element handler.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\sitestudio_data_transformers\Unit\Handlers\ElementLevel\GoogleMapHandlerTest
 *
 * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\GoogleMapHandler
 */
class GoogleMapHandlerTest extends ElementHandlerTestBase {

  const JSON_VALUES = '{"canvas":[{"type":"container","uid":"google-map","title":"Google map","status":{"collapsed":false},"uuid":"2460af48-964a-4f64-8e21-bff8a0768979","parentUid":"root","children":[]}],"mapper":{"2460af48-964a-4f64-8e21-bff8a0768979":{"settings":{"formDefinition":[{"formKey":"google-map-settings","children":[{"formKey":"google-map-type","activeFields":[{"name":"mapType","active":true},{"name":"zoomLevel","active":true}]},{"formKey":"google-map-default-marker","activeFields":[{"name":"markerType","active":true},{"name":"googleMarker","active":true}]},{"formKey":"google-map-controls","activeFields":[{"name":"scrollWheel","active":true},{"name":"mapTypeControl","active":true},{"name":"scaleControl","active":true},{"name":"draggable","active":true},{"name":"zoomControl","active":true}]},{"formKey":"google-map-aspect-ratio","breakpoints":[],"activeFields":[{"name":"aspectRatio","active":true}]}]}],"selectorType":"topLevel","form":null,"items":[],"title":"Default"}}},"model":{"2460af48-964a-4f64-8e21-bff8a0768979":{"settings":{"mapType":"default","zoomLevel":"12","markerType":"default","aspectRatio":"4by3","title":"Google map","mapControls":{"mapTypeControl":true,"zoomControl":true}}}},"previewModel":{"2460af48-964a-4f64-8e21-bff8a0768979":{}},"variableFields":{"2460af48-964a-4f64-8e21-bff8a0768979":[]},"meta":{}}';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->handler = new GoogleMapHandler($this->moduleHandler);
  }

  /**
   * @covers \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\GoogleMap::getData
   */
  public function testGetData() {
    $this->result = [
      'type' => 'google-map',
      'id' => '2460af48-964a-4f64-8e21-bff8a0768979',
      'data' => [
        'title' => 'Google map',
        'mapType' => 'default',
        'zoomLevel' => '12',
        'markerType' => 'default',
      ],
    ];
    parent::testGetData();
  }


}
