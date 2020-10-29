<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityJsonValuesTrait;
use Drupal\cohesion\Plugin\EntityUpdate\_0029EntityUpdate;
use Drupal\Tests\UnitTestCase;


/**
 * Class MockUpdateEntity
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0029MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0029EntityUpdateUnitTest extends UnitTestCase {

  /** @var $unit _0029MockUpdateEntity  */
  protected $unit;

  private $fixture_layout = '{"canvas":[{"type":"item","uid":"button","title":"Button","status":{"collapsed":true},"uuid":"21748e2a-4a73-4034-acce-b47cecc3197b","parentUid":"root","isContainer":false,"children":[]}],"componentForm":[],"mapper":{"21748e2a-4a73-4034-acce-b47cecc3197b":{}},"model":{"21748e2a-4a73-4034-acce-b47cecc3197b":{"settings":{"title":"Button","customStyle":[{"customStyle":""}],"settings":{"type":"interaction","styles":{"xl":{"buttonAnimation":[{"animationType":"none"}]}},"customStyle":[{"customStyle":""}]},"type":"interaction","modifier":[{"modifierType":"","interactionScope":"document"}],"styles":{"xl":{"buttonAnimation":[{"animationType":"none"}]}}},"context-visibility":{"contextVisibility":{"condition":"ALL"}},"styles":{"settings":{"element":"button"}}}},"previewModel":{"21748e2a-4a73-4034-acce-b47cecc3197b":{}},"variableFields":{"21748e2a-4a73-4034-acce-b47cecc3197b":[]},"meta":{"fieldHistory":[]}}';

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    $this->unit = new _0029EntityUpdate([], null, null);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0029EntityUpdate::runUpdate
   */
  public function testRunUpdate() {

    // WYSIWYG in layout canvas
    $layout = new _0014MockUpdateEntity($this->fixture_layout, TRUE);
    $this->assertionsLayoutCanvasBefore($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
    $this->unit->runUpdate($layout);
    $this->assertionsLayoutCanvasAfter($layout->getDecodedJsonValues());
  }

  private function assertionsLayoutCanvasBefore($layout_array_before){
    $this->assertEquals("button", $layout_array_before['canvas'][0]['uid'], 'button uid' );
    $this->assertEquals(false, $layout_array_before['canvas'][0]['isContainer'], 'button isContainer' );
    $this->assertEquals("item", $layout_array_before['canvas'][0]['type'], 'button type' );
    $this->assertEquals(["collapsed" => true], $layout_array_before['canvas'][0]['status'], 'button children status');
  }

  private function assertionsLayoutCanvasAfter($layout_array_after){
    $this->assertEquals("button", $layout_array_after['canvas'][0]['uid'], 'button uid' );
    $this->assertEquals(true, $layout_array_after['canvas'][0]['isContainer'], 'button isContainer' );
    $this->assertEquals("container", $layout_array_after['canvas'][0]['type'], 'button type' );
    $this->assertEquals([], $layout_array_after['canvas'][0]['children'], 'button children');
    $this->assertEquals(["collapsed" => true], $layout_array_after['canvas'][0]['status'], 'button children status');
  }
}
