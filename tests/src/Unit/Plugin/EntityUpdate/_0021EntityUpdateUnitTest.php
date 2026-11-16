<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0021EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0021MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0021EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0021MockUpdateEntity*/
  protected $unit;

  private $fixture_sgm = '{
  "model": {
    "c314c9fb-8cf2-49ae-bcb6-dea9269fbc02": {
      "settings": {
        "type": "cohSection",
        "title": "Field group",
        "hideRowHeading": 0,
        "columnCount": "coh-component-field-group-1-col",
        "breakpoints": false,
        "propertiesMenu": false,
        "disableScrollbar": true,
        "disableEllipsisMenu": true,
        "isOpen": true
      }
    },
    "443cd744-1b2f-4f92-ac7e-d02948cfd5e0": {
      "settings": {
        "title": "Field group",
        "type": "cohSection",
        "hideRowHeading": 1,
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
    }
  },
  "mapper": {
    "443cd744-1b2f-4f92-ac7e-d02948cfd5e0": {}
  },
  "previewModel": {
    "443cd744-1b2f-4f92-ac7e-d02948cfd5e0": {}
  },
  "variableFields": {
    "443cd744-1b2f-4f92-ac7e-d02948cfd5e0": []
  },
  "styleGuideForm": [
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
      "children": [],
      "uuid": "c314c9fb-8cf2-49ae-bcb6-dea9269fbc02",
      "parentUid": "root",
      "isContainer": true
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
      "children": [],
      "uuid": "443cd744-1b2f-4f92-ac7e-d02948cfd5e0",
      "parentUid": "root",
      "isContainer": true
    }
  ]
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0021EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0021EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0021MockUpdateEntity($this->fixture_sgm, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertArrayNotHasKey('removePadding', $layout_array_before['model']['c314c9fb-8cf2-49ae-bcb6-dea9269fbc02']['settings']);
    $this->assertArrayNotHasKey('removePadding', $layout_array_before['model']['443cd744-1b2f-4f92-ac7e-d02948cfd5e0']['settings']);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertArrayHasKey('removePadding', $layout_array_after['model']['c314c9fb-8cf2-49ae-bcb6-dea9269fbc02']['settings']);
    $this->assertArrayHasKey('removePadding', $layout_array_after['model']['443cd744-1b2f-4f92-ac7e-d02948cfd5e0']['settings']);

    $this->assertEquals(0, $layout_array_after['model']['c314c9fb-8cf2-49ae-bcb6-dea9269fbc02']['settings']['removePadding']);
    $this->assertEquals(1, $layout_array_after['model']['443cd744-1b2f-4f92-ac7e-d02948cfd5e0']['settings']['removePadding']);
  }

}
