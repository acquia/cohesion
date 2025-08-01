<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0024EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0024MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {

}

/**
 * @group Cohesion
 */
class _0024EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0024MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{
  "model": {
    "65630114-5f4d-4b4a-b64d-ddabf25939d2": {
      "settings": {
        "title": "Text input",
        "schema": {
          "type": "string",
          "escape": true
        },
        "tooltipPlacement": "auto right"
      },
      "contextVisibility": {
        "condition": "ALL"
      }
    },
    "2a833616-6f1c-4d57-9ec5-5ba256e55c8d": {
      "settings": {
        "title": "Text input",
        "schema": {
          "type": "string",
          "escape": true
        },
        "tooltipPlacement": "auto right"
      },
      "contextVisibility": {
        "condition": "ALL"
      }
    },
    "425c503b-d51f-42fb-9363-6a3d6d1a2eec": {
      "settings": {
        "title": "Text input",
        "schema": {
          "type": "string",
          "escape": true
        },
        "tooltipPlacement": "auto right"
      },
      "contextVisibility": {
        "condition": "ALL"
      }
    },
    "462fcaef-8891-42ae-b7aa-bd3c6aa4ce35": {
      "settings": {
        "type": "cohAccordion",
        "title": "Group accordion",
        "htmlClass": "coh-accordion-panel-body--dark",
        "isOpen": true
      }
    },
    "1c6b530c-ff58-4833-86a3-6547b8d4e0c6": {
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
    "040017e5-f70b-49cd-b26c-131f394b34ef": {
      "settings": {
        "title": "Paragraph",
        "customStyle": [
          {
            "customStyle": ""
          }
        ],
        "content": "[field.65630114-5f4d-4b4a-b64d-ddabf25939d2]"
      },
      "context-visibility": {
        "contextVisibility": {
          "condition": "ALL"
        }
      },
      "styles": {
        "settings": {
          "element": "p"
        }
      }
    },
    "3290e880-17ab-44b9-8bde-651e1d063c12": {
      "settings": {
        "title": "Paragraph",
        "customStyle": [
          {
            "customStyle": ""
          }
        ],
        "content": "[field.2a833616-6f1c-4d57-9ec5-5ba256e55c8d]"
      },
      "context-visibility": {
        "contextVisibility": {
          "condition": "ALL"
        }
      },
      "styles": {
        "settings": {
          "element": "p"
        }
      }
    },
    "3fab3076-28ad-4d2b-aed3-1da53179dec8": {
      "settings": {
        "title": "Paragraph",
        "customStyle": [
          {
            "customStyle": ""
          }
        ],
        "content": "[field.425c503b-d51f-42fb-9363-6a3d6d1a2eec]"
      },
      "context-visibility": {
        "contextVisibility": {
          "condition": "ALL"
        }
      },
      "styles": {
        "settings": {
          "element": "p"
        }
      }
    }
  },
  "previewModel": {
    "040017e5-f70b-49cd-b26c-131f394b34ef": {},
    "3290e880-17ab-44b9-8bde-651e1d063c12": {},
    "3fab3076-28ad-4d2b-aed3-1da53179dec8": {},
    "65630114-5f4d-4b4a-b64d-ddabf25939d2": {},
    "2a833616-6f1c-4d57-9ec5-5ba256e55c8d": {},
    "425c503b-d51f-42fb-9363-6a3d6d1a2eec": {}
  },
  "variableFields": {
    "040017e5-f70b-49cd-b26c-131f394b34ef": [
      "settings.content"
    ],
    "3290e880-17ab-44b9-8bde-651e1d063c12": [
      "settings.content"
    ],
    "3fab3076-28ad-4d2b-aed3-1da53179dec8": [
      "settings.content"
    ],
    "65630114-5f4d-4b4a-b64d-ddabf25939d2": [],
    "2a833616-6f1c-4d57-9ec5-5ba256e55c8d": [],
    "425c503b-d51f-42fb-9363-6a3d6d1a2eec": []
  },
  "canvas": [
    {
      "type": "container",
      "uid": "paragraph",
      "title": "Paragraph",
      "status": {
        "collapsed": true
      },
      "children": [],
      "uuid": "040017e5-f70b-49cd-b26c-131f394b34ef",
      "parentUid": "root",
      "isContainer": true
    },
    {
      "type": "container",
      "uid": "paragraph",
      "title": "Paragraph",
      "status": {
        "collapsed": true
      },
      "children": [],
      "uuid": "3290e880-17ab-44b9-8bde-651e1d063c12",
      "parentUid": "root",
      "isContainer": true
    },
    {
      "type": "container",
      "uid": "paragraph",
      "title": "Paragraph",
      "status": {
        "collapsed": true
      },
      "children": [],
      "uuid": "3fab3076-28ad-4d2b-aed3-1da53179dec8",
      "parentUid": "root",
      "isContainer": true
    }
  ],
  "componentForm": [
    {
      "type": "form-field",
      "uid": "form-input",
      "title": "Input",
      "status": {
        "collapsed": false
      },
      "uuid": "65630114-5f4d-4b4a-b64d-ddabf25939d2",
      "parentUid": "root",
      "humanId": "Field 1",
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
          "type": "form-field",
          "uid": "form-input",
          "title": "Input",
          "status": {
            "collapsed": false
          },
          "uuid": "2a833616-6f1c-4d57-9ec5-5ba256e55c8d",
          "parentUid": "form-accordion",
          "humanId": "Field 2",
          "isContainer": false
        },
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
              "type": "form-field",
              "uid": "form-input",
              "title": "Input",
              "status": {
                "collapsed": false
              },
              "uuid": "425c503b-d51f-42fb-9363-6a3d6d1a2eec",
              "parentUid": "form-section",
              "humanId": "Field 3",
              "isContainer": false
            }
          ],
          "uuid": "1c6b530c-ff58-4833-86a3-6547b8d4e0c6",
          "parentUid": "form-accordion",
          "isContainer": true
        }
      ],
      "uuid": "462fcaef-8891-42ae-b7aa-bd3c6aa4ce35",
      "parentUid": "root",
      "isContainer": true
    }
  ]
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0024EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0024EntityUpdate::machineNameFromTitle
   */
  public function testMachineNameFromTitle() {

    // Capital letters.
    $this->assertEquals('capitals', $this->unit->machineNameFromTitle('CapiTalS'));

    // Some special characters.
    $this->assertEquals('peclchrcters', $this->unit->machineNameFromTitle('$pec|@lch@r@cters'));

    // All special characters.
    $this->assertEquals('field', $this->unit->machineNameFromTitle('%£^ $ %& ^** () *(*&^£  $'));

    // Starts with spaces.
    $this->assertEquals('test', $this->unit->machineNameFromTitle('    test'));

    // Contains spaces and special characters.
    $this->assertEquals('my-machine-name', $this->unit->machineNameFromTitle(' My! Machine! Name! '));
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0024EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $layout = new _0024MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    // Remove all humanId keys from componentForm fields.
    $this->assertArrayHasKey('humanId', $layout_array_before['componentForm'][0]);
    $this->assertArrayHasKey('humanId', $layout_array_before['componentForm'][1]['children'][0]);
    $this->assertArrayHasKey('humanId', $layout_array_before['componentForm'][1]['children'][1]['children'][0]);

    // Assign all existing fields a unique machine name.
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['65630114-5f4d-4b4a-b64d-ddabf25939d2']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['2a833616-6f1c-4d57-9ec5-5ba256e55c8d']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['425c503b-d51f-42fb-9363-6a3d6d1a2eec']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['462fcaef-8891-42ae-b7aa-bd3c6aa4ce35']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['1c6b530c-ff58-4833-86a3-6547b8d4e0c6']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['040017e5-f70b-49cd-b26c-131f394b34ef']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['3290e880-17ab-44b9-8bde-651e1d063c12']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_before['model']['3fab3076-28ad-4d2b-aed3-1da53179dec8']['settings']);

    // Build up meta.fieldHistory object for every component.
    $this->assertArrayNotHasKey('meta', $layout_array_before);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertArrayNotHasKey('humanId', $layout_array_after['componentForm'][0]);
    $this->assertArrayNotHasKey('humanId', $layout_array_after['componentForm'][1]['children'][0]);
    $this->assertArrayNotHasKey('humanId', $layout_array_after['componentForm'][1]['children'][1]['children'][0]);

    // Assign all existing fields a unique machine name.
    $this->assertEquals('text-input', $layout_array_after['model']['65630114-5f4d-4b4a-b64d-ddabf25939d2']['settings']['machineName']);
    $this->assertEquals('text-input2', $layout_array_after['model']['2a833616-6f1c-4d57-9ec5-5ba256e55c8d']['settings']['machineName']);
    $this->assertEquals('text-input3', $layout_array_after['model']['425c503b-d51f-42fb-9363-6a3d6d1a2eec']['settings']['machineName']);
    $this->assertArrayNotHasKey('machineName', $layout_array_after['model']['462fcaef-8891-42ae-b7aa-bd3c6aa4ce35']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_after['model']['1c6b530c-ff58-4833-86a3-6547b8d4e0c6']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_after['model']['040017e5-f70b-49cd-b26c-131f394b34ef']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_after['model']['3290e880-17ab-44b9-8bde-651e1d063c12']['settings']);
    $this->assertArrayNotHasKey('machineName', $layout_array_after['model']['3fab3076-28ad-4d2b-aed3-1da53179dec8']['settings']);

    // Build up meta.fieldHistory object for every component.
    $this->assertEquals([
      'uuid' => '65630114-5f4d-4b4a-b64d-ddabf25939d2',
      'type' => 'form-input',
      'machineName' => 'text-input',
    ], $layout_array_after['meta']['fieldHistory'][0]);

    $this->assertEquals([
      'uuid' => '2a833616-6f1c-4d57-9ec5-5ba256e55c8d',
      'type' => 'form-input',
      'machineName' => 'text-input2',
    ], $layout_array_after['meta']['fieldHistory'][1]);

    $this->assertEquals([
      'uuid' => '425c503b-d51f-42fb-9363-6a3d6d1a2eec',
      'type' => 'form-input',
      'machineName' => 'text-input3',
    ], $layout_array_after['meta']['fieldHistory'][2]);
  }

}
