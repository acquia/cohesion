<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0039EntityUpdate;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;

/**
 * @group Cohesion
 */
class _0039EntityUpdateUnitTest extends EntityUpdateUnitTestCase {


  protected $unit;

  /**
   * {@inheritdoc}
   */
  public function setUp() : void {
    $this->unit = new _0039EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0039EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $custom_style = new CustomStyle(['id' => 'wysiwyg', 'class_name' => '.coh-child-class', 'parent' => 'parent-id', 'available_in_wysiwyg' => TRUE], 'cohesion_custom_style');

    // The initial values are what we expected.
    $this->assertEquals($custom_style->get('available_in_wysiwyg'), TRUE);
    $this->assertNotEquals($custom_style->get('available_in_wysiwyg_inline'), TRUE);

    // Run the update.
    $this->unit->runUpdate($custom_style);

    // Check the child now references the parent by classname.
    $this->assertEquals($custom_style->get('available_in_wysiwyg'), TRUE);
    $this->assertEquals($custom_style->get('available_in_wysiwyg_inline'), TRUE);
  }
}
