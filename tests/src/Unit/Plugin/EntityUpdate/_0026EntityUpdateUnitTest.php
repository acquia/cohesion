<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0026EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0026MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0026EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0026MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{ "model": { "b83a2851-280f-4622-9cae-885101060938": { "settings": { "dropzoneHideSelector": "" } }, "2502eef1-4cd6-445f-ac67-826a97b2eef2": { "settings": { "width": "fluid" } }, "b1685282-990e-4375-abb6-58a5d524a1f9": { "settings": { "styles": { "xl": { "bleed": "retain", "targetContainer": "inner", "overflow": "visible", "jsSettings": { "matchHeightRow": { "targetElement": "none" } } } } } }, "5071523a-87c1-42e2-a60f-48e82a7b1f78": { "settings": { "title": "Column", "styles": { "xl": { "col": 6 } }, "settings": { "styles": { "xl": { "col": -2, "pull": -1, "push": -1, "offset": -1 } } } }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "column" } } }, "c80ac344-5279-4c50-aa70-64c9298eb673": { "settings": { "title": "Column", "styles": { "xl": { "col": 3 } }, "settings": { "styles": { "xl": { "col": -2, "pull": -1, "push": -1, "offset": -1 } } } }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "column" } } }, "c2fb7427-8639-4b64-a1c2-ed0a1c2ba441": { "settings": { "title": "Column", "styles": { "xl": { "col": 11 } }, "settings": { "styles": { "xl": { "col": -2, "pull": -1, "push": -1, "offset": -1 } } } }, "context-visibility": { "contextVisibility": { "condition": "ALL" } }, "styles": { "settings": { "element": "column" } } } }, "mapper": { "b83a2851-280f-4622-9cae-885101060938": {}, "2502eef1-4cd6-445f-ac67-826a97b2eef2": {}, "b1685282-990e-4375-abb6-58a5d524a1f9": {}, "5071523a-87c1-42e2-a60f-48e82a7b1f78": {}, "c80ac344-5279-4c50-aa70-64c9298eb673": { "settings": { "formDefinition": [ { "formKey": "column-settings", "children": [ { "formKey": "column-width-and-position", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "col", "active": true }, { "name": "pull", "active": false }, { "name": "push", "active": false }, { "name": "offset", "active": false } ] }, { "formKey": "common-link-animation", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "linkAnimation", "active": true }, { "name": "animationType", "active": true }, { "name": "animationScope", "active": true }, { "name": "animationParent", "active": true }, { "name": "animationTarget", "active": true }, { "name": "animationScale", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDistance", "active": true }, { "name": "animationPieces", "active": true }, { "name": "animationOrigin", "active": true }, { "name": "animationFoldHeight", "active": true }, { "name": "animationHorizontalFirst", "active": true }, { "name": "animationIterations", "active": true }, { "name": "animationEasing", "active": true }, { "name": "animationDuration", "active": true } ] }, { "formKey": "common-link-modifier", "breakpoints": [], "activeFields": [ { "name": "modifier", "active": true }, { "name": "modifierType", "active": true }, { "name": "interactionScope", "active": true }, { "name": "interactionParent", "active": true }, { "name": "interactionTarget", "active": true }, { "name": "modifierName", "active": true } ] } ] } ], "title": "Settings", "selectorType": "topLevel", "form": null, "items": [] } }, "c2fb7427-8639-4b64-a1c2-ed0a1c2ba441": { "settings": { "formDefinition": [ { "formKey": "column-settings", "children": [ { "formKey": "column-width-and-position", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "col", "active": true }, { "name": "pull", "active": false }, { "name": "push", "active": false }, { "name": "offset", "active": false } ] }, { "formKey": "common-link-animation", "breakpoints": [ { "name": "xl" } ], "activeFields": [ { "name": "linkAnimation", "active": true }, { "name": "animationType", "active": true }, { "name": "animationScope", "active": true }, { "name": "animationParent", "active": true }, { "name": "animationTarget", "active": true }, { "name": "animationScale", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDirection", "active": true }, { "name": "animationDistance", "active": true }, { "name": "animationPieces", "active": true }, { "name": "animationOrigin", "active": true }, { "name": "animationFoldHeight", "active": true }, { "name": "animationHorizontalFirst", "active": true }, { "name": "animationIterations", "active": true }, { "name": "animationEasing", "active": true }, { "name": "animationDuration", "active": true } ] }, { "formKey": "common-link-modifier", "breakpoints": [], "activeFields": [ { "name": "modifier", "active": true }, { "name": "modifierType", "active": true }, { "name": "interactionScope", "active": true }, { "name": "interactionParent", "active": true }, { "name": "interactionTarget", "active": true }, { "name": "modifierName", "active": true } ] } ] } ], "title": "Settings", "selectorType": "topLevel", "form": null, "items": [] } } }, "previewModel": { "c80ac344-5279-4c50-aa70-64c9298eb673": {}, "c2fb7427-8639-4b64-a1c2-ed0a1c2ba441": {}, "5071523a-87c1-42e2-a60f-48e82a7b1f78": {} }, "variableFields": { "c80ac344-5279-4c50-aa70-64c9298eb673": [], "c2fb7427-8639-4b64-a1c2-ed0a1c2ba441": [], "5071523a-87c1-42e2-a60f-48e82a7b1f78": [] }, "meta": {}, "canvas": [ { "type": "item", "uid": "component-drop-zone-placeholder", "title": "Component drop zone", "status": { "collapsed": true }, "uuid": "b83a2851-280f-4622-9cae-885101060938", "parentUid": "root", "isContainer": false }, { "type": "container", "uid": "container", "title": "Container", "status": { "collapsed": false }, "children": [ { "type": "container", "uid": "row-for-columns", "title": "Row for columns", "status": { "collapsed": false }, "children": [ { "type": "container", "uid": "column", "title": "Column", "status": { "collapsed": false }, "children": [], "breakpointClasses": "coh-layout-col-xl-6 coh-layout-column-width", "uuid": "5071523a-87c1-42e2-a60f-48e82a7b1f78", "parentUid": "row-for-columns", "isContainer": true }, { "type": "container", "uid": "column", "title": "Column", "status": { "collapsed": false }, "children": [], "breakpointClasses": "coh-layout-col-xl-3 coh-layout-column-width", "uuid": "c80ac344-5279-4c50-aa70-64c9298eb673", "parentUid": "row-for-columns", "isContainer": true }, { "type": "container", "uid": "column", "title": "Column", "status": { "collapsed": false }, "children": [], "breakpointClasses": "coh-layout-col-xl-11 coh-layout-column-width", "uuid": "c2fb7427-8639-4b64-a1c2-ed0a1c2ba441", "parentUid": "row-for-columns", "isContainer": true } ], "uuid": "b1685282-990e-4375-abb6-58a5d524a1f9", "parentUid": "container", "isContainer": true } ], "uuid": "2502eef1-4cd6-445f-ac67-826a97b2eef2", "parentUid": "root", "isContainer": true } ], "componentForm": [] }';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0026EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0026EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0026MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertArrayHasKey('breakpointClasses', $layout_array_before['canvas'][1]['children'][0]['children'][0]);
    $this->assertArrayHasKey('breakpointClasses', $layout_array_before['canvas'][1]['children'][0]['children'][1]);
    $this->assertArrayHasKey('breakpointClasses', $layout_array_before['canvas'][1]['children'][0]['children'][2]);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertArrayNotHasKey('breakpointClasses', $layout_array_after['canvas'][1]['children'][0]['children'][0]);
    $this->assertArrayNotHasKey('breakpointClasses', $layout_array_after['canvas'][1]['children'][0]['children'][1]);
    $this->assertArrayNotHasKey('breakpointClasses', $layout_array_after['canvas'][1]['children'][0]['children'][2]);
  }

}
