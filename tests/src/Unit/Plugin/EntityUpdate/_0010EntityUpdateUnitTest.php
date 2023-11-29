<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0010EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0010MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0010EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0010MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{"model":{"62bdeef6-a6a1-4dda-81d1-05f2f9316fa2":{"settings":{"type":"cohFileBrowser","options":{"buttonText":"Select image","imageUploader":false,"allowedDescription":"Allowed: png, gif, jpg, jpeg \nMax file size: 2MB","removeLabel":"Remove"},"title":"Image uploader","isStyle":true,"defaultActive":true,"schema":{"type":"image"}}}},"mapper":{},"previewModel":{},"canvas":[],"componentForm":[{"type":"form-field","uid":"form-image","title":"Image uploader","parentIndex":"form-fields","status":{"collapsed":false},"parentUid":"root","uuid":"62bdeef6-a6a1-4dda-81d1-05f2f9316fa2","humanId":"Field 2","isContainer":false}]}';

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0010EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0010EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0010MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals("image", $layout_array_before['model']['62bdeef6-a6a1-4dda-81d1-05f2f9316fa2']['settings']['schema']['type'], 'image');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals("string", $layout_array_after['model']['62bdeef6-a6a1-4dda-81d1-05f2f9316fa2']['settings']['schema']['type'], 'image');
  }

}
