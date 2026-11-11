<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0038EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0038MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0038EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * Component json_value.
   *
   * @var string
   */
  private $fixture_component = '{    "canvas": [],    "disabledNodes": [],    "componentForm": [        {            "type": "form-container",            "uid": "form-accordion",            "title": "Group accordion",            "status": {                "collapsed": false            },            "options": {                "formBuilder": true            },            "iconColor": "formLayout",            "uuid": "4675d394-0cfc-4bb6-911e-899b1dfc149e",            "parentUid": "root",            "children": []        },        {            "type": "form-field-container",            "uid": "form-field-repeater",            "title": "Field repeater",            "selected": false,            "status": {                "collapsed": false,                "isopen": false            },            "options": {                "formBuilder": true            },            "iconColor": "formLayoutAlt",            "uuid": "898d7bcc-cab5-4f26-864e-cbd12d6dbef0",            "parentUid": "root",            "children": []        },        {            "type": "form-container",            "uid": "form-section",            "title": "Field group",            "status": {                "collapsed": false            },            "options": {                "formBuilder": true            },            "iconColor": "formLayoutAlt",            "uuid": "f45d399e-58fc-4163-b77c-13a40f3f84f3",            "parentUid": "root",            "children": []        },        {            "type": "form-container",            "uid": "form-section",            "title": "Field group",            "status": {                "collapsed": false            },            "options": {                "formBuilder": true            },            "iconColor": "formLayoutAlt",            "uuid": "ec9ea6a7-2946-491c-bcb9-6f7c9964043d",            "parentUid": "root",            "children": []        },        {            "type": "form-container",            "uid": "form-section",            "title": "Field group",            "status": {                "collapsed": false            },            "options": {                "formBuilder": true            },            "iconColor": "formLayoutAlt",            "uuid": "06cd27ce-9f84-430b-8f41-5775b305c726",            "parentUid": "root",            "children": []        }    ],    "model": {        "4675d394-0cfc-4bb6-911e-899b1dfc149e": {            "settings": {                "type": "cohAccordion",                "title": "Group accordion",                "htmlClass": "coh-accordion-panel-body--dark",                "isOpen": true            }        },        "898d7bcc-cab5-4f26-864e-cbd12d6dbef0": {            "settings": {                "type": "cohArray",                "title": "Field repeater",                "componentField": true,                "noTitle": true,                "htmlClass": "coh-array--field-repeater",                "disableScrollbar": true,                "addText": "Add",                "key": "field-repeater",                "sortable": true,                "sortableOptions": {                    "axis": "y",                    "handle": true                },                "schema": {                    "type": "array"                }            }        },        "f45d399e-58fc-4163-b77c-13a40f3f84f3": {            "settings": {                "title": "Field group",                "type": "cohSection",                "hideRowHeading": 0,                "removePadding": 0,                "columnCount": "coh-component-field-group-1-col",                "breakpointIcon": "coh-breakpoint-icon coh-icon-television",                "breakpoints": false,                "propertiesMenu": false,                "disableScrollbar": true,                "disableEllipsisMenu": true,                "isOpen": true            },            "contextVisibility": {                "condition": "ALL"            }        },        "ec9ea6a7-2946-491c-bcb9-6f7c9964043d": {            "settings": {                "title": "Field group",                "type": "cohSection",                "hideRowHeading": 0,                "removePadding": 0,                "columnCount": "coh-component-field-group-3-col",                "breakpointIcon": "coh-breakpoint-icon coh-icon-laptop",                "breakpoints": false,                "propertiesMenu": false,                "disableScrollbar": true,                "disableEllipsisMenu": true,                "isOpen": true            },            "contextVisibility": {                "condition": "ALL"            }        },        "06cd27ce-9f84-430b-8f41-5775b305c726": {            "settings": {                "title": "Field group",                "type": "cohSection",                "hideRowHeading": 0,                "removePadding": 0,                "columnCount": "coh-component-field-group-col",                "breakpointIcon": "",                "breakpoints": false,                "propertiesMenu": false,                "disableScrollbar": true,                "disableEllipsisMenu": true,                "isOpen": true            },            "contextVisibility": {                "condition": "ALL"            }        }    },    "mapper": {},    "previewModel": {        "f45d399e-58fc-4163-b77c-13a40f3f84f3": {},        "ec9ea6a7-2946-491c-bcb9-6f7c9964043d": {},        "06cd27ce-9f84-430b-8f41-5775b305c726": {}    },    "variableFields": {        "f45d399e-58fc-4163-b77c-13a40f3f84f3": [],        "ec9ea6a7-2946-491c-bcb9-6f7c9964043d": [],        "06cd27ce-9f84-430b-8f41-5775b305c726": []    },    "meta": {        "lastModifiedEl": "ec9ea6a7-2946-491c-bcb9-6f7c9964043d",        "fieldHistory": []    }}';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->unit = new _0038EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0038EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Test a Component form.
    $componentLayoutCanvas = new _0038MockUpdateEntity($this->fixture_component, TRUE);
    $this->assertionsComponentLayoutCanvasBefore($componentLayoutCanvas->getDecodedJsonValues());
    $this->unit->runUpdate($componentLayoutCanvas);
    $this->assertionsComponentLayoutCanvasAfter($componentLayoutCanvas->getDecodedJsonValues());
  }

  private function assertionsComponentLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals('coh-accordion-panel-body--dark', $layout_array_before['model']['4675d394-0cfc-4bb6-911e-899b1dfc149e']['settings']['htmlClass']);

    $this->assertEquals('coh-array--field-repeater', $layout_array_before['model']['898d7bcc-cab5-4f26-864e-cbd12d6dbef0']['settings']['htmlClass']);

    $this->assertEquals('coh-component-field-group-1-col', $layout_array_before['model']['f45d399e-58fc-4163-b77c-13a40f3f84f3']['settings']['columnCount']);
    $this->assertEquals('coh-breakpoint-icon coh-icon-television', $layout_array_before['model']['f45d399e-58fc-4163-b77c-13a40f3f84f3']['settings']['breakpointIcon']);

    $this->assertEquals('coh-component-field-group-3-col', $layout_array_before['model']['ec9ea6a7-2946-491c-bcb9-6f7c9964043d']['settings']['columnCount']);
    $this->assertEquals('coh-breakpoint-icon coh-icon-laptop', $layout_array_before['model']['ec9ea6a7-2946-491c-bcb9-6f7c9964043d']['settings']['breakpointIcon']);

    $this->assertEquals('coh-component-field-group-col', $layout_array_before['model']['06cd27ce-9f84-430b-8f41-5775b305c726']['settings']['columnCount']);
    $this->assertEquals('', $layout_array_before['model']['06cd27ce-9f84-430b-8f41-5775b305c726']['settings']['breakpointIcon']);
  }

  private function assertionsComponentLayoutCanvasAfter($layout_array_after) {
    $this->assertArrayNotHasKey('htmlClass', $layout_array_after['model']['4675d394-0cfc-4bb6-911e-899b1dfc149e']['settings']);

    $this->assertArrayNotHasKey('htmlClass', $layout_array_after['model']['898d7bcc-cab5-4f26-864e-cbd12d6dbef0']['settings']);

    $this->assertEquals('1', $layout_array_after['model']['f45d399e-58fc-4163-b77c-13a40f3f84f3']['settings']['columnCount']);
    $this->assertEquals('television', $layout_array_after['model']['f45d399e-58fc-4163-b77c-13a40f3f84f3']['settings']['breakpointIcon']);

    $this->assertEquals('3', $layout_array_after['model']['ec9ea6a7-2946-491c-bcb9-6f7c9964043d']['settings']['columnCount']);
    $this->assertEquals('laptop', $layout_array_after['model']['ec9ea6a7-2946-491c-bcb9-6f7c9964043d']['settings']['breakpointIcon']);

    $this->assertEquals('auto', $layout_array_after['model']['06cd27ce-9f84-430b-8f41-5775b305c726']['settings']['columnCount']);
    $this->assertEquals('', $layout_array_after['model']['06cd27ce-9f84-430b-8f41-5775b305c726']['settings']['breakpointIcon']);
  }
}
