<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Plugin\EntityUpdate\_0004EntityUpdate;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockUpdateEntity extends EntityMockBase implements EntityJsonValuesInterface {
}

/**
 * @group Cohesion
 */
class _0004EntityUpdateUnitTest extends EntityUpdateUnitTestCase {

  protected $unit;

  /**
   * {@inheritdoc}
   */
  public function setUp():void {
    // Create a mock of the Php uuid generator service.
    // $prophecy = $this->prophesize(Php::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    // $uuid_service_mock = $prophecy->reveal();
    $this->unit = new _0004EntityUpdate([], NULL, NULL);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\EntityUpdate\_0004EntityUpdate::runUpdate
   */
  public function testRunUpdate() {
    $start_json_values = '{\/settings-endpoint\/}';
    $expected_json_values = '{\/cohesionapi\/}';
    $entity = new MockUpdateEntity($start_json_values);

    $this->unit->runUpdate($entity);

    $this->assertEquals($entity->getJsonValues(), $expected_json_values);
  }

}
