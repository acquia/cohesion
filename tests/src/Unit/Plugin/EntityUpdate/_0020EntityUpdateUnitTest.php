<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0020EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0020MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0020EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0020MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{
    "canvas": [],
    "componentForm": [
        {
            "type": "form-container",
            "uid": "form-tab-container",
            "title": "Tab container",
            "parentIndex": "form-layout",
            "status": {
                "collapsed": false
            },
            "options": {
                "formBuilder": true
            },
            "uuid": "4971c66f-6ea4-47a9-88ba-84eb818e40ae",
            "parentUid": "root",
            "isContainer": true,
            "children": [
                {
                    "type": "form-container",
                    "uid": "form-tab-item",
                    "title": "Tab item",
                    "parentIndex": "form-layout",
                    "status": {
                        "collapsed": false
                    },
                    "options": {
                        "formBuilder": true
                    },
                    "uuid": "28aef32d-0e98-4167-a1a2-60f9ee617591",
                    "parentUid": "form-tab-container",
                    "isContainer": true,
                    "children": [
                        {
                            "type": "form-container",
                            "uid": "form-section",
                            "title": "Field group",
                            "parentIndex": "form-layout",
                            "status": {
                                "collapsed": true
                            },
                            "options": {
                                "formBuilder": true
                            },
                            "uuid": "3fc18f56-8e5c-46a7-941a-0dc80e33004a",
                            "parentUid": "form-tab-item",
                            "isContainer": true,
                            "children": [
                                {
                                    "type": "form-help",
                                    "uid": "form-helptext",
                                    "title": "Help text",
                                    "parentIndex": "form-help",
                                    "status": {
                                        "collapsed": false,
                                        "collapsedParents": [
                                            "3fc18f56-8e5c-46a7-941a-0dc80e33004a"
                                        ]
                                    },
                                    "uuid": "048ee42a-8224-43c5-ba43-628d843bbc82",
                                    "parentUid": "form-section",
                                    "isContainer": false,
                                    "children": []
                                }
                            ]
                        }
                    ]
                }
            ]
        }
    ],
    "model": {
        "4971c66f-6ea4-47a9-88ba-84eb818e40ae": {
            "settings": {
                "type": "cohTabContainer",
                "title": "Tab container"
            }
        },
        "28aef32d-0e98-4167-a1a2-60f9ee617591": {
            "settings": {
                "title": "Help",
                "type": "cohTabItem"
            },
            "contextVisibility": {
                "condition": "ALL"
            }
        },
        "3fc18f56-8e5c-46a7-941a-0dc80e33004a": {
            "settings": {
                "title": "Help and information",
                "type": "cohSection",
                "hideRowHeading": 0,
                "columnCount": "coh-component-field-group-1-col",
                "breakpoints": false,
                "propertiesMenu": false,
                "disableScrollbar": true,
                "disableEllipsisMenu": true,
                "isOpen": true
            },
            "contextVisibility": {
                "condition": "ALL"
            }
        },
        "048ee42a-8224-43c5-ba43-628d843bbc82": {
            "settings": {
                "title": "Help text",
                "type": "cohHelpText"
            },
            "contextVisibility": {
                "condition": "ALL"
            },
            "model": {
                "value": "This component should only be used at the top of \'Landing pages\'."
            }
        }
    },
    "mapper": {},
    "previewModel": {
        "28aef32d-0e98-4167-a1a2-60f9ee617591": {},
        "048ee42a-8224-43c5-ba43-628d843bbc82": {},
        "3fc18f56-8e5c-46a7-941a-0dc80e33004a": {}
    },
    "variableFields": {}
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0020EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0020EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0020MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals('This component should only be used at the top of \'Landing pages\'.', $layout_array_before['model']['048ee42a-8224-43c5-ba43-628d843bbc82']['model']['value']);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals('This component should only be used at the top of \'Landing pages\'.', $layout_array_after['model']['048ee42a-8224-43c5-ba43-628d843bbc82']['settings']['options']['helpText']);
    $this->assertArrayNotHasKey('model', $layout_array_after['model']['048ee42a-8224-43c5-ba43-628d843bbc82']['settings']);
  }

}
