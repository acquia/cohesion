<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0017EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0017MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0017EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0017MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{
  "canvas": [
    {
      "type": "container",
      "uid": "video-background",
      "title": "Video background",
      "status": {
        "collapsed": false
      },
      "parentIndex": 3,
      "parentUid": "root",
      "uuid": "a99b43c5-381e-49ff-92a5-e474128a31c5",
      "isContainer": true,
      "children": []
    }
  ],
  "componentForm": [
    {
      "type": "form-field",
      "uid": "form-video-embed",
      "title": "Video",
      "parentIndex": "form-fields",
      "status": {
        "collapsed": false
      },
      "parentUid": "root",
      "uuid": "50505cbc-2b01-4caf-8803-0258c5ad0e98",
      "humanId": "Field 1",
      "isContainer": false,
      "children": []
    }
  ],
  "model": {
    "a99b43c5-381e-49ff-92a5-e474128a31c5": {
      "settings": {
        "title": "Video background",
        "videoBackgroundPauseHidden": true,
        "videoBackgroundDisableTouch": false,
        "videoBackgroundScale": "coh-video-background-center",
        "customStyle": [
          {
            "customStyle": ""
          }
        ],
        "videoBackgroundUrl": "[field.5cb08c24-0500-4e20-a771-33991a6c4ea2]"
      },
      "context-visibility": {
        "contextVisibility": {
          "condition": "ALL"
        }
      },
      "styles": {
        "settings": {
          "element": "video-background"
        }
      }
    },
    "50505cbc-2b01-4caf-8803-0258c5ad0e98": {
      "settings": {
        "type": "cohMediaEmbed",
        "title": "Video URL",
        "schema": {
          "type": "string"
        }
      }
    }
  },
  "mapper": {}
}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0017EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0017EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0017MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertArrayNotHasKey("options", $layout_array_before['model']['50505cbc-2b01-4caf-8803-0258c5ad0e98']['settings']);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals(TRUE, $layout_array_after['model']['50505cbc-2b01-4caf-8803-0258c5ad0e98']['settings']['options']['noPlugin']);
  }

}
