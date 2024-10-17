<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0036EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0036MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0036EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * Layout json_value.
   *
   * @var string
   */
  private $fixture_layout_canvas = '{"canvas":[{"uid":"cpt_dfgxg","type":"component","title":"dfgxg","enabled":true,"category":"category-2","componentId":"cpt_dfgxg","componentType":"heading","iconColor":"custom","uuid":"e90f89de-42f8-42bb-9626-ea63382e16f8","children":[],"parentUid":"root","status":{}}],"model":{"e90f89de-42f8-42bb-9626-ea63382e16f8":{}},"mapper":{},"previewModel":{},"variableFields":{},"meta":{"lastModifiedEl":"e90f89de-42f8-42bb-9626-ea63382e16f8"}}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0036EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0036EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // Test a Layout Canvas.
    $layoutCanvas = new _0036MockUpdateEntity($this->fixture_layout_canvas, TRUE);
    $this->assertionsLayoutCanvasBefore($layoutCanvas->getDecodedJsonValues());
    $this->unit->runUpdate($layoutCanvas);
    $this->assertionsLayoutCanvasAfter($layoutCanvas->getDecodedJsonValues());
    $this->unit->runUpdate($layoutCanvas);
    $this->assertionsLayoutCanvasAfter($layoutCanvas->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals('e90f89de-42f8-42bb-9626-ea63382e16f8', $layout_array_before['meta']['lastModifiedEl']);
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertArrayNotHasKey('lastModifiedEl', $layout_array_after['meta']);
  }
}
