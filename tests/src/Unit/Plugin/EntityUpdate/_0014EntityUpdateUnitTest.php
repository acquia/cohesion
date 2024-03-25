<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0014EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0014MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0014EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  /**
   * @var unit_0014MockUpdateEntity*/
  protected $unit;

  private $fixture_layout = '{"model":{"507c1624-455f-4e31-9603-329adf1640f2":{"settings":{"title":"Link","customStyle":[{"customStyle":""}],"type":"url","scrollToDuration":450,"styles":{"xl":{"linkAnimation":[{"animationType":"none","animationScope":"document","animationScale":null,"animationDirection":"up","animationOrigin":"top,center","animationHorizontalFirst":false,"animationEasing":"swing"}]}},"target":"_self","linkText":"Link text","titleAttribute":"Title","url":"https:\/\/cohesiondx.com"},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"link"}}}},"mapper":{"507c1624-455f-4e31-9603-329adf1640f2":{}},"previewModel":{"507c1624-455f-4e31-9603-329adf1640f2":{}},"canvas":[{"type":"item","uid":"link","title":"Link","status":{"collapsed":true},"parentIndex":0,"uuid":"507c1624-455f-4e31-9603-329adf1640f2","parentUid":"root","isContainer":false}]}';

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    $this->unit = new _0014EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0014EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas.
    $layout = new _0014MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before) {
    $this->assertEquals("link", $layout_array_before['canvas'][0]['uid'], 'link uid');
    $this->assertEquals(FALSE, $layout_array_before['canvas'][0]['isContainer'], 'link isContainer');
    $this->assertEquals("item", $layout_array_before['canvas'][0]['type'], 'link type');
    $this->assertArrayNotHasKey('children', $layout_array_before['canvas'][0], 'link children');
    $this->assertEquals(["collapsed" => TRUE], $layout_array_before['canvas'][0]['status'], 'link children status');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after) {
    $this->assertEquals("link", $layout_array_after['canvas'][0]['uid'], 'link uid');
    $this->assertEquals(TRUE, $layout_array_after['canvas'][0]['isContainer'], 'link isContainer');
    $this->assertEquals("container", $layout_array_after['canvas'][0]['type'], 'link type');
    $this->assertEquals([], $layout_array_after['canvas'][0]['children'], 'link children');
    $this->assertEquals(["collapsed" => TRUE], $layout_array_after['canvas'][0]['status'], 'link children status');
  }

}
