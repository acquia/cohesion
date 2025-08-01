<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0011EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0011MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0011EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0011MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{"model":{"a7110a38-5abe-4096-8c67-e41eab35eadb":{"settings":{"type":"cohSelect","isStyle":true,"nullOption":false,"defaultValue":false,"options":[{"label":"All closed","value":true},{"label":"First open","value":false}],"schema":["string","number","boolean"],"selectType":"existing","selectModel":["settings","accordion_tabs_container","accordion-tabs-container-start-state","startCollapsed"],"title":"Select"},"contextVisibility":{"condition":"ALL"},"model":{}}},"mapper":{},"previewModel":{"a7110a38-5abe-4096-8c67-e41eab35eadb":{}},"canvas":[],"componentForm":[{"type":"form-field","uid":"form-select","title":"Select","parentIndex":"form-fields","status":{"collapsed":false},"uuid":"a7110a38-5abe-4096-8c67-e41eab35eadb","parentUid":"root","humanId":"Field 1","isContainer":false}]}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0011EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0011EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0011MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals(["string", "number", "boolean"], $layout_array_before['model']['a7110a38-5abe-4096-8c67-e41eab35eadb']['settings']['schema'], 'schema');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals(['type' => ["string", "number", "boolean"]], $layout_array_after['model']['a7110a38-5abe-4096-8c67-e41eab35eadb']['settings']['schema'], 'schema');
  }

}
