<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Plugin\EntityUpdate\_0002EntityUpdate;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;

/**
 * Class _0002EntityUpdateMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate
 */
class _0002EntityUpdateMock extends _0002EntityUpdate {

  public function getCustomStyle($parent_id) {
    $parent_custom_style = new CustomStyle(['id' => 'parent', 'class_name' => '.coh-parent-class', 'parent' => FALSE], 'cohesion_custom_style');
    return $parent_custom_style;
  }

}

/**
 * @group Cohesion
 */
class _0002EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  protected $unit;

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    $this->unit = new _0002EntityUpdateMock([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0002EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $child_custom_style = new CustomStyle(['id' => 'child', 'class_name' => '.coh-child-class', 'parent' => 'parent-id'], 'cohesion_custom_style');

    // The initial values are what we expected.
    $this->assertEquals($child_custom_style->getClass(), '.coh-child-class');
    $this->assertEquals($child_custom_style->getParent(), 'parent-id');

    // Run the update.
    $this->unit->runUpdate($child_custom_style);

    // Check the child now references the parent by classname.
    $this->assertEquals($child_custom_style->getClass(), '.coh-child-class');
    $this->assertEquals($child_custom_style->getParent(), '.coh-parent-class');
  }

}
