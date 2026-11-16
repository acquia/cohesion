<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0023EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0023MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0023EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0023MockUpdateEntity*/
  protected $unit;

  private $fixture_form = '{
  "model": {
    "6c675468-60fc-4881-b3f7-1d0201c02140": {
      "settings": {
        "type": "cohTabContainer",
        "title": "Tab container"
      }
    },
    "d92c56c2-7445-4424-9746-4883e94957b2": {
      "settings": {
        "type": "cohAccordion",
        "title": "Group accordion",
        "htmlClass": "coh-accordion-panel-body--dark",
        "isOpen": true
      }
    },
    "581ca7e6-93b1-4ea9-8110-cdc62f8c5120": {
      "settings": {
        "type": "cohSection",
        "title": "Field group",
        "hideRowHeading": 0,
        "removePadding": 0,
        "columnCount": "coh-component-field-group-1-col",
        "breakpoints": false,
        "propertiesMenu": false,
        "disableScrollbar": true,
        "disableEllipsisMenu": true,
        "isOpen": true
      }
    },
    "9fb38262-abc4-40d5-a714-2f9be08420c6": {
      "settings": {
        "type": "cohTabItem",
        "title": "Tab item"
      }
    },
    "93a97ccc-a7a0-48cf-b2f9-dc4a2d5050d8": {
      "settings": {
        "type": "cohHelpText",
        "title": "Help text"
      }
    }
  },
  "mapper": {},
  "previewModel": {},
  "variableFields": {},
  "canvas": [],
  "componentForm": [
    {
      "type": "form-help",
      "uid": "form-helptext",
      "title": "Help text",
      "status": {
        "collapsed": false
      },
      "uuid": "93a97ccc-a7a0-48cf-b2f9-dc4a2d5050d8",
      "parentUid": "root",
      "isContainer": false
    },
    {
      "type": "form-container",
      "uid": "form-accordion",
      "title": "Group accordion",
      "status": {
        "collapsed": false
      },
      "options": {
        "formBuilder": true
      },
      "children": [
        {
          "type": "form-container",
          "uid": "form-section",
          "title": "Field group",
          "status": {
            "collapsed": false
          },
          "options": {
            "formBuilder": true
          },
          "children": [
            {
              "type": "form-container",
              "uid": "form-tab-container",
              "title": "Tab container",
              "status": {
                "collapsed": false
              },
              "options": {
                "formBuilder": true
              },
              "children": [
                {
                  "type": "form-container",
                  "uid": "form-tab-item",
                  "title": "Tab item",
                  "status": {
                    "collapsed": false
                  },
                  "options": {
                    "formBuilder": true
                  },
                  "children": [],
                  "uuid": "9fb38262-abc4-40d5-a714-2f9be08420c6",
                  "parentUid": "form-tab-container",
                  "isContainer": true
                }
              ],
              "uuid": "6c675468-60fc-4881-b3f7-1d0201c02140",
              "parentUid": "form-section",
              "isContainer": true
            }
          ],
          "uuid": "581ca7e6-93b1-4ea9-8110-cdc62f8c5120",
          "parentUid": "form-accordion",
          "isContainer": true
        }
      ],
      "uuid": "d92c56c2-7445-4424-9746-4883e94957b2",
      "parentUid": "root",
      "isContainer": true
    }
  ]
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0023EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0023EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0023MockUpdateEntity($this->fixture_form, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertArrayNotHasKey('responsiveMode', $layout_array_before['model']['93a97ccc-a7a0-48cf-b2f9-dc4a2d5050d8']['settings']);
    $this->assertArrayNotHasKey('breakpointIcon', $layout_array_before['model']['93a97ccc-a7a0-48cf-b2f9-dc4a2d5050d8']['settings']);

    $this->assertArrayNotHasKey('responsiveMode', $layout_array_before['model']['6c675468-60fc-4881-b3f7-1d0201c02140']['settings']);
    $this->assertArrayNotHasKey('breakpointIcon', $layout_array_before['model']['581ca7e6-93b1-4ea9-8110-cdc62f8c5120']['settings']);
    $this->assertArrayNotHasKey('breakpointIcon', $layout_array_before['model']['9fb38262-abc4-40d5-a714-2f9be08420c6']['settings']);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertArrayNotHasKey('responsiveMode', $layout_array_after['model']['93a97ccc-a7a0-48cf-b2f9-dc4a2d5050d8']['settings']);
    $this->assertArrayNotHasKey('breakpointIcon', $layout_array_after['model']['93a97ccc-a7a0-48cf-b2f9-dc4a2d5050d8']['settings']);

    $this->assertArrayHasKey('responsiveMode', $layout_array_after['model']['6c675468-60fc-4881-b3f7-1d0201c02140']['settings']);
    $this->assertArrayHasKey('breakpointIcon', $layout_array_after['model']['581ca7e6-93b1-4ea9-8110-cdc62f8c5120']['settings']);
    $this->assertArrayHasKey('breakpointIcon', $layout_array_after['model']['9fb38262-abc4-40d5-a714-2f9be08420c6']['settings']);

    $this->assertEquals(TRUE, $layout_array_after['model']['6c675468-60fc-4881-b3f7-1d0201c02140']['settings']['responsiveMode']);
    $this->assertEquals("", $layout_array_after['model']['581ca7e6-93b1-4ea9-8110-cdc62f8c5120']['settings']['breakpointIcon']);
    $this->assertEquals("", $layout_array_after['model']['9fb38262-abc4-40d5-a714-2f9be08420c6']['settings']['breakpointIcon']);
  }

}
