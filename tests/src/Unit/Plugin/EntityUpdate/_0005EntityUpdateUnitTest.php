<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0005EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0005MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

class _0005EntityUpdateMock extends _0005EntityUpdate {

  private $fixture_component = '{ "model": { "3b16eed6-43cd-438f-b8be-a3ea7a41ac27": { "settings": { "type": "cohWysiwyg", "title": "WYSIWYG", "schema": { "type": "string" } } }, "59fd194d-f8ae-44a4-b13a-30762a07493d": { "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "wysiwyg" } }, "settings": { "content": "[field.3b16eed6-43cd-438f-b8be-a3ea7a41ac27]" }, "isVariableMode": false } }, "mapper": { "59fd194d-f8ae-44a4-b13a-30762a07493d": {} }, "canvas": [ { "type": "item", "uid": "wysiwyg", "title": "WYSIWYG", "status": { "collapsed": true }, "parentIndex": 1, "parentUid": "root", "uuid": "59fd194d-f8ae-44a4-b13a-30762a07493d", "isContainer": false } ], "componentForm": [ { "type": "form-field", "uid": "form-wysiwyg", "title": "WYSIWYG", "parentIndex": "form-fields", "status": { "collapsed": false }, "parentUid": "root", "uuid": "3b16eed6-43cd-438f-b8be-a3ea7a41ac27", "humanId": "Field 1", "isContainer": false } ] }';

  public function loadComponent($componentId) {
    return new _0005MockUpdateEntity($this->fixture_component, TRUE);
  }

  public function getCustomElementFields($uid) {
    return [
      'mywysiwygfield' => [
        'htmlClass' => 'col-xs-12',
        'type' => 'wysiwyg',
        'title' => 'Title of my WYSIWYG field.',
      ],
    ];
  }

}

/**
 * @group Cohesion
 */
class _0005EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0005EntityUpdateMock*/
  protected $unit;

  private $fixture_layout = '{ "model": { "4851d279-eeaa-4e72-97b0-64a1e1c54e07": { "settings": { "mywysiwygfield": "<p>WYSIWYG content custom element</p>\n" }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "example_element" } }, "isVariableMode": false }, "61a5de69-0b60-432c-a892-777d7528f832": { "settings": { "title": "test WYSIWYG update script - component" }, "isVariableMode": false, "3b16eed6-43cd-438f-b8be-a3ea7a41ac27": "<p>WYSIWYG value as component field</p>\n" }, "c4cb21f3-cdcb-4167-9dcb-3e3bb764a500": { "settings": { "markerType": "default", "link": { "type": "none" }, "markerInfo": "<p>Test Google map marker info window value</p>\n", "infoWindowClass": "", "title": "Google map marker - test value" }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "google-map-marker" } }, "isVariableMode": false }, "960ef4fd-d2f7-4b91-aae1-29f51d721e9b": { "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "wysiwyg" } }, "settings": { "content": "<p>test WYSIWYG value</p>\n", "title": "WYSIWYG - test value" }, "isVariableMode": false }, "7412e1ed-defa-4e42-84eb-c5cff04e44eb": { "settings": { "type": "cohWysiwyg", "title": "WYSIWYG - test default value", "schema": { "type": "string" } }, "contextVisibility": { "condition": "ALL" }, "model": { "value": "<p>default wysiwyg value</p>\n" } } }, "mapper": { "4851d279-eeaa-4e72-97b0-64a1e1c54e07": {}, "c4cb21f3-cdcb-4167-9dcb-3e3bb764a500": { "settings": { "topLevel": { "formDefinition": [ { "formKey": "google-map-marker-settings", "children": [ { "formKey": "google-map-marker-label", "breakpoints": [], "activeFields": [ { "name": "label", "active": true } ] }, { "formKey": "google-map-marker-location", "breakpoints": [], "activeFields": [ { "name": "latlong", "active": true } ] }, { "formKey": "google-map-marker-marker", "breakpoints": [], "activeFields": [ { "name": "markerType", "active": true }, { "name": "googleMarker", "active": true }, { "name": "googleMarkerScaledSizeX", "active": true }, { "name": "googleMarkerScaledSizeY", "active": true } ] }, { "formKey": "google-map-marker-link", "breakpoints": [], "activeFields": [ { "name": "type", "active": true }, { "name": "linkToPage", "active": true }, { "name": "url", "active": true }, { "name": "anchor", "active": true } ] }, { "formKey": "google-map-marker-info-window", "breakpoints": [], "activeFields": [ { "name": "markerInfo", "active": true }, { "name": "infoWindowClass", "active": true } ] } ] } ], "selectorType": "topLevel", "form": null }, "dropzone": [], "selectorType": "topLevel" } }, "960ef4fd-d2f7-4b91-aae1-29f51d721e9b": {} }, "canvas": [ { "type": "item", "uid": "wysiwyg", "title": "WYSIWYG", "status": { "collapsed": true }, "parentIndex": 1, "parentUid": "root", "uuid": "960ef4fd-d2f7-4b91-aae1-29f51d721e9b", "isContainer": false }, { "type": "item", "uid": "google-map-marker", "title": "Google map marker", "status": { "collapsed": true }, "parentIndex": 4, "parentUid": "root", "uuid": "c4cb21f3-cdcb-4167-9dcb-3e3bb764a500", "isContainer": false }, { "uid": "c13dfe81", "type": "component", "title": "test WYSIWYG update script - component", "enabled": true, "category": "general", "componentId": "c13dfe81", "componentType": "wysiwyg", "preview_image": { "url": false }, "parentIndex": 0, "parentUid": "root", "uuid": "61a5de69-0b60-432c-a892-777d7528f832", "isContainer": 0, "children": [], "status": {} }, { "type": "item", "uid": "example_element", "isCustom": true, "title": "Example element", "selected": false, "status": { "collapsed": true }, "parentIndex": 8, "parentUid": "root", "uuid": "4851d279-eeaa-4e72-97b0-64a1e1c54e07", "isContainer": false } ], "componentForm": [ { "type": "form-field", "uid": "form-wysiwyg", "title": "WYSIWYG", "parentIndex": "form-fields", "status": { "collapsed": false }, "parentUid": "root", "uuid": "7412e1ed-defa-4e42-84eb-c5cff04e44eb", "humanId": "Field 1", "isContainer": false } ]}';
  private $fixture_style = '{ "preview": "<h1 class=\"coh-preview\">WYSIYG content style preview</h1>\n", "sBackgroundColour": "#FFFFFF", "styles": { "settings": { "element": "h1", "class": "", "combinator": "", "pseudo": "" } } }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0005EntityUpdateMock([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0005EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0005MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());

    // WYSIWYG preview in styles.
    $style = new _0005MockUpdateEntity($this->fixture_style, FALSE);
    $style_array_before = $style->getDecodedJsonValues();
    $this->assertEquals("<h1 class=\"coh-preview\">WYSIYG content style preview</h1>\n", $style_array_before['preview'], 'before style preview');
    $this->unit->runUpdate($style);
    $this->assertAfterStyle($style->getDecodedJsonValues());
    $this->unit->runUpdate($style);
    $this->assertAfterStyle($style->getDecodedJsonValues());

  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals("<p>WYSIWYG content custom element</p>\n", $layout_array_before['model']['4851d279-eeaa-4e72-97b0-64a1e1c54e07']['settings']['mywysiwygfield'], 'before custom element');
    $this->assertEquals("<p>WYSIWYG value as component field</p>\n", $layout_array_before['model']['61a5de69-0b60-432c-a892-777d7528f832']['3b16eed6-43cd-438f-b8be-a3ea7a41ac27'], 'before component value');
    $this->assertEquals("<p>Test Google map marker info window value</p>\n", $layout_array_before['model']['c4cb21f3-cdcb-4167-9dcb-3e3bb764a500']['settings']['markerInfo'], 'before google map marker');
    $this->assertEquals("<p>test WYSIWYG value</p>\n", $layout_array_before['model']['960ef4fd-d2f7-4b91-aae1-29f51d721e9b']['settings']['content'], 'before wysiwyg element');
    $this->assertEquals("<p>default wysiwyg value</p>\n", $layout_array_before['model']['7412e1ed-defa-4e42-84eb-c5cff04e44eb']['model']['value'], 'before default component value');
    $this->assertEquals("string", $layout_array_before['model']['7412e1ed-defa-4e42-84eb-c5cff04e44eb']['settings']['schema']['type'], 'before component schema type');

  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals([
      'text' => "<p>WYSIWYG content custom element</p>\n",
      'textFormat' => 'cohesion',
    ],
      $layout_array_after['model']['4851d279-eeaa-4e72-97b0-64a1e1c54e07']['settings']['mywysiwygfield'],
      'after custom element'
    );

    $this->assertEquals([
      'text' => "<p>WYSIWYG value as component field</p>\n",
      'textFormat' => 'cohesion',
    ],
      $layout_array_after['model']['61a5de69-0b60-432c-a892-777d7528f832']['3b16eed6-43cd-438f-b8be-a3ea7a41ac27'],
      'after component value'
    );

    $this->assertEquals([
      'text' => "<p>Test Google map marker info window value</p>\n",
      'textFormat' => 'cohesion',
    ],
      $layout_array_after['model']['c4cb21f3-cdcb-4167-9dcb-3e3bb764a500']['settings']['markerInfo'],
      'after google map marker'
    );

    $this->assertEquals([
      'text' => "<p>test WYSIWYG value</p>\n",
      'textFormat' => 'cohesion',
    ],
      $layout_array_after['model']['960ef4fd-d2f7-4b91-aae1-29f51d721e9b']['settings']['content'],
      'after wysiwyg element'
    );

    $this->assertEquals([
      'text' => "<p>default wysiwyg value</p>\n",
      'textFormat' => 'cohesion',
    ],
      $layout_array_after['model']['7412e1ed-defa-4e42-84eb-c5cff04e44eb']['model']['value'],
      'after default component value'
    );

    $this->assertEquals("object", $layout_array_after['model']['7412e1ed-defa-4e42-84eb-c5cff04e44eb']['settings']['schema']['type'], 'after component schema type');
  }

  private function assertAfterStyle($style_array_after) {
    $this->assertEquals([
      'text' => "<h1 class=\"coh-preview\">WYSIYG content style preview</h1>\n",
      'textFormat' => 'cohesion',
    ],
      $style_array_after['preview'],
      'after style preview'
    );
  }

}
