<?php

namespace Drupal\Tests\cohesion_base_styles\Unit\Entity;

use Drupal\cohesion_base_styles\Entity\BaseStyles;
use Drupal\Tests\UnitTestCase;

/**
 * @group Cohesion
 */
class BaseStylesTest extends UnitTestCase {

  /**
   * Checks base styles are ordered as we expect in the JSON and therefore in
   * the stylesheet.
   * Site Studio "default" ones first and then user created ones and in
   * reverse alphabetical order.
   *
   * @covers \Drupal\cohesion_base_styles\Entity\BaseStyles::reorderStyles
   */
  public function testReorderStyles() {

    $original_style_order = [
      'base_anchor_links' => [],
      'base_back_to_top' => [],
      'body' => [],
      'blockquote' => [],
      'heading_1' => [],
    ];

    $expected_style_order = [
      'heading_1' => [],
      'body' => [],
      'blockquote' => [],
      'base_back_to_top' => [],
      'base_anchor_links' => [],
    ];

    $result = BaseStyles::reorderStyles($original_style_order);
    $this->assertSame($expected_style_order, $result);
  }

}
