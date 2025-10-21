<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Plugin\EntityUpdate\_0008EntityUpdate;
use Drupal\cohesion_elements\Entity\CohesionElementEntityBase;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockCategoryUpdateEntity extends CohesionElementEntityBase {
  protected $category;

  public function __construct($start_category) {
    $this->category = $start_category;
  }

  public function getCategory() {
    return $this->category;
  }

  public function setCategory($category) {
    $this->category = $category;
  }

}

class MockComponentCategoryUpdateEntity extends MockCategoryUpdateEntity {
  const ASSET_GROUP_ID = 'component';

}

class MockHelperCategoryUpdateEntity extends MockCategoryUpdateEntity {
  const ASSET_GROUP_ID = 'helper';

}

/**
 * @group Cohesion
 */
class _0008EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  protected $unit;

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    // Create a mock of the Php uuid generator service.
    // $prophecy = $this->prophesize(Php::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    // $uuid_service_mock = $prophecy->reveal();
    $this->unit = new _0008EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0008EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    // Test update from old style.
    $start_category = 'general';
    $expected_category = 'cpt_cat_general_components';
    $entity = new MockComponentCategoryUpdateEntity($start_category);

    $this->unit->runUpdate($entity);

    $this->assertEquals($expected_category, $entity->getCategory());

    // Test nothing found - keep current category reference.
    $start_category = 'somethingunknown';
    $expected_category = 'somethingunknown';
    $entity = new MockComponentCategoryUpdateEntity($start_category);

    $this->unit->runUpdate($entity);

    $this->assertEquals($expected_category, $entity->getCategory());

    // Test update from old style.
    $start_category = 'interactive';
    $expected_category = 'hlp_cat_interactive_helpers';
    $entity = new MockHelperCategoryUpdateEntity($start_category);

    $this->unit->runUpdate($entity);

    $this->assertEquals($expected_category, $entity->getCategory());

    // Test nothing found - keep current category reference.
    $start_category = 'something else unknown';
    $expected_category = 'something else unknown';
    $entity = new MockHelperCategoryUpdateEntity($start_category);

    $this->unit->runUpdate($entity);

    $this->assertEquals($expected_category, $entity->getCategory());
  }

}
