<?php

namespace Drupal\Tests\Update\cohesion\Unit;

use Drupal\cohesion\EntityUpdateInterface;
use Drupal\cohesion\EntityUpdateManager;
use Drupal\cohesion\EntityUpdatePluginManager;
use Drupal\Tests\UnitTestCase;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockUpdateEntity implements EntityUpdateInterface {
  protected $last_update;
  protected $is_new;

  public function __construct($last_update, $is_new) {
    $this->last_update = $last_update;
    $this->is_new = $is_new;
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAppliedUpdate() {
    return $this->last_update;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastAppliedUpdate($callback) {
    $this->last_update = $callback;
  }

  public function isNew() {
    return $this->is_new;
  }

}

/**
 * Class MockPluginInstance.
 *
 * @package Drupal\Tests\Update\cohesion\Unit
 */
class MockPluginInstance {

  public function runUpdate(&$entity) {
    return TRUE;
  }

}

/**
 * Class MockEntityUpdateManager.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockEntityUpdateManager extends EntityUpdateManager {

  public function getAllPluginDefinitions() {
    return [
      'entityupdate_0002' => [
        'id' => 'entityupdate_0002',
      ],
      'entityupdate_0003' => [
        'id' => 'entityupdate_0003',
      ],
      'entityupdate_0006' => [
        'id' => 'entityupdate_0006',
      ],
    ];
  }

  public function getPluginInstance($plugin_id) {
    return new MockPluginInstance();
  }

}

/**
 * @group Cohesion
 */
class EntityUpdateManagerUnitTest extends UnitTestCase {

  /**
   * @var MockEntityUpdateManager*/
  protected $mockUnit;

  /**
   * Before a test method is run, setUp() is invoked.
   * Create new unit object.
   */
  public function setUp(): void {
    // Create a mock of the EntityUpdatePluginManager service.
    $prophecy = $this->prophesize(EntityUpdatePluginManager::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    $update_update_plugin_manager = $prophecy->reveal();

    $this->mockUnit = new MockEntityUpdateManager($update_update_plugin_manager);

  }

  /**
   * @covers \Drupal\cohesion\EntityUpdateManager::getAllPluginDefinitions
   */
  public function testGetAllPlugins() {
    $plugins = $this->mockUnit->getAllPluginDefinitions();

    $this->assertEquals(count($plugins), 3);
    $this->assertEquals(isset($plugins['entityupdate_0002']), TRUE);
    $this->assertEquals(isset($plugins['entityupdate_0003']), TRUE);
    $this->assertEquals(isset($plugins['entityupdate_0006']), TRUE);
    $this->assertEquals(isset($plugins['entityupdate_0007']), FALSE);

  }

  /**
   * @covers \Drupal\cohesion\EntityUpdateManager::apply
   */
  public function testApply() {
    // For a new entity, just set the update to the latest callback function name.
    $mock_entity = new MockUpdateEntity(NULL, TRUE);
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), NULL);

    $applied_callbacks = $this->mockUnit->apply($mock_entity);

    $this->assertEquals(count($applied_callbacks), 0);
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'entityupdate_0006');

    // For an existing entity, only apply updates after the found callback.
    $mock_entity = new MockUpdateEntity('entityupdate_0003', FALSE);
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'entityupdate_0003');

    $applied_callbacks = $this->mockUnit->apply($mock_entity);

    $this->assertEquals(count($applied_callbacks), 1);
    $this->assertEquals($applied_callbacks[0], 'entityupdate_0006');
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'entityupdate_0006');

    // For an existing entity, only apply updates after the found callback.
    $mock_entity = new MockUpdateEntity('entityupdate_0002', FALSE);
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'entityupdate_0002');

    $applied_callbacks = $this->mockUnit->apply($mock_entity);

    $this->assertEquals(count($applied_callbacks), 2);
    $this->assertEquals($applied_callbacks[0], 'entityupdate_0003');
    $this->assertEquals($applied_callbacks[1], 'entityupdate_0006');
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'entityupdate_0006');

    // For an existing entity, only apply updates after the found callback.
    $mock_entity = new MockUpdateEntity('unknowncallback', FALSE);
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'unknowncallback');

    $applied_callbacks = $this->mockUnit->apply($mock_entity);

    $this->assertEquals(count($applied_callbacks), 3);
    $this->assertEquals($applied_callbacks[0], 'entityupdate_0002');
    $this->assertEquals($applied_callbacks[1], 'entityupdate_0003');
    $this->assertEquals($applied_callbacks[2], 'entityupdate_0006');
    $this->assertEquals($mock_entity->getLastAppliedUpdate(), 'entityupdate_0006');
  }

  /**
   * Once test method has finished running, whether it succeeded or failed, tearDown() will be invoked.
   * Unset the $unit object.
   */
  public function tearDown(): void {
    unset($this->mockUnit);
  }

}
