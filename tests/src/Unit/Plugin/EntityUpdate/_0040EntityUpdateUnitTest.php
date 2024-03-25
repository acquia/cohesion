<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0040EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class _0040MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0040EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * Custom style json_value.
   *
   * @var string
   */
  private $fixture_custom_style = '{"preview":{"text":"<p>Default content for `Generic`.</p>\n","textFormat":"cohesion"},"styles":{"settings":{"element":"div","class":"","combinator":"","pseudo":""}},"sBackgroundColour":"#ff1200","variableFields":[]}';

  /**
   * Base style json_value.
   *
   * @var string
   */
  private $fixture_base_style = '{"preview":{"text":"<p>Add your preview HTML element here.</p>\n","textFormat":"cohesion"},"styles":{"settings":{"element":"test"}},"sBackgroundColour":"#1700b9","variableFields":[]}';

  /**
   * Base style json_value where sBackground is an object .
   *
   * @var string
   */
  private $fixture_base_style_object = '{"preview":{"text":"<p>Default content for `Generic`.</p>\n","textFormat":"cohesion"},"styles":{"settings":{"element":"div","class":"","combinator":"","pseudo":""},"styles":{"xl":{"custom-css":[{"customCssProperty":{"value":"float"},"customCss":{"value":"left"}}]}}},"sBackgroundColour":{"value":{"hex":"#FFFFFF","rgba":"rgba(255, 255, 255, 1)"}},"variableFields":[]}';

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    $this->unit = new _0040EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0040EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $custom_style = new _0040MockUpdateEntity($this->fixture_custom_style, FALSE);
    $this->assertionsCustomStyleBefore($custom_style->getDecodedJsonValues());
    $this->unit->runUpdate($custom_style);
    $this->assertionsCustomStyleAfter($custom_style->getDecodedJsonValues());

    $base_style = new _0040MockUpdateEntity($this->fixture_base_style, FALSE);
    $this->assertionsBaseStyleBefore($base_style->getDecodedJsonValues());
    $this->unit->runUpdate($base_style);
    $this->assertionsBaseStyleAfter($base_style->getDecodedJsonValues());

    $base_style_object = new _0040MockUpdateEntity($this->fixture_base_style_object, FALSE);
    $this->assertionsBaseStyleObjectBefore($base_style_object->getDecodedJsonValues());
    $this->unit->runUpdate($base_style_object);
    $this->assertionsBaseStyleObjectAfter($base_style_object->getDecodedJsonValues());
  }

  private function assertionsCustomStyleBefore($layout_array_before) {
    $this->assertEquals('#ff1200', $layout_array_before['sBackgroundColour']);
  }

  private function assertionsCustomStyleAfter($layout_array_after) {
    $this->assertEquals('rgba(255,18,0,1)', $layout_array_after['preview']['background']['value']['rgba']);
    $this->assertArrayNotHasKey('sBackgroundColour', $layout_array_after);
  }

  private function assertionsBaseStyleBefore($layout_array_before) {
    $this->assertEquals('#1700b9', $layout_array_before['sBackgroundColour']);
  }

  private function assertionsBaseStyleAfter($layout_array_after) {
    $this->assertEquals('rgba(23,0,185,1)', $layout_array_after['preview']['background']['value']['rgba']);
    $this->assertArrayNotHasKey('sBackgroundColour', $layout_array_after);
  }

  private function assertionsBaseStyleObjectBefore($layout_array_before) {
    $this->assertEquals('#FFFFFF', $layout_array_before['sBackgroundColour']['value']['hex']);
    $this->assertEquals('rgba(255, 255, 255, 1)', $layout_array_before['sBackgroundColour']['value']['rgba']);
  }

  private function assertionsBaseStyleObjectAfter($layout_array_after) {
    $this->assertEquals('rgba(255,255,255,1)', $layout_array_after['preview']['background']['value']['rgba']);
    $this->assertArrayNotHasKey('sBackgroundColour', $layout_array_after);
  }

}
