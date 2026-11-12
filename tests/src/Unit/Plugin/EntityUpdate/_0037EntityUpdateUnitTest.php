<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0037EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0037MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0037EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * Component json_value.
   *
   * @var string
   */
  private $fixture_component = '{    "canvas": [],    "disabledNodes": [],    "componentForm": [        {            "type": "form-help",            "uid": "form-helptext",            "title": "Help text",            "status": {                "collapsed": false            },            "iconColor": "formHelp",            "uuid": "54544602-1781-4c7e-9cb6-1b7ae94c3cd1",            "parentUid": "root"        },        {            "type": "form-help",            "uid": "form-helptext",            "title": "Help text",            "status": {                "collapsed": false            },            "iconColor": "formHelp",            "uuid": "123ff572-561e-4295-83e0-df129b7b3108",            "parentUid": "root"        }    ],    "model": {        "54544602-1781-4c7e-9cb6-1b7ae94c3cd1": {            "settings": {                "title": "Help text",                "type": "cohHelpText",                "options": {                    "showClose": false,                    "helpType": "coh-help-text--help"                }            }        },        "123ff572-561e-4295-83e0-df129b7b3108": {            "settings": {                "title": "Help text",                "type": "cohHelpText",                "options": {                    "showClose": false,                    "helpType": "coh-help-text--warning"                }            },            "contextVisibility": {                "condition": "ALL"            }        }    },    "mapper": {},    "previewModel": {        "123ff572-561e-4295-83e0-df129b7b3108": {}    },    "variableFields": {        "123ff572-561e-4295-83e0-df129b7b3108": []    },    "meta": {        "lastModifiedEl": "123ff572-561e-4295-83e0-df129b7b3108"    }}';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->unit = new _0037EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0037EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Test a Component form.
    $componentLayoutCanvas = new _0037MockUpdateEntity($this->fixture_component, TRUE);
    $this->assertionsComponentLayoutCanvasBefore($componentLayoutCanvas->getDecodedJsonValues());
    $this->unit->runUpdate($componentLayoutCanvas);
    $this->assertionsComponentLayoutCanvasAfter($componentLayoutCanvas->getDecodedJsonValues());
  }

  private function assertionsComponentLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals('coh-help-text--help', $layout_array_before['model']['54544602-1781-4c7e-9cb6-1b7ae94c3cd1']['settings']['options']['helpType']);
    $this->assertEquals('coh-help-text--warning', $layout_array_before['model']['123ff572-561e-4295-83e0-df129b7b3108']['settings']['options']['helpType']);
  }

  private function assertionsComponentLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals('help', $layout_array_after['model']['54544602-1781-4c7e-9cb6-1b7ae94c3cd1']['settings']['options']['helpType']);
    $this->assertEquals('warning', $layout_array_after['model']['123ff572-561e-4295-83e0-df129b7b3108']['settings']['options']['helpType']);
  }
}
