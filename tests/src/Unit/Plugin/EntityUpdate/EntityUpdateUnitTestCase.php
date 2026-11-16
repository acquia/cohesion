<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\Tests\UnitTestCase;
use Symfony\Component\Process\Exception\LogicException;

/**
 * Extends UnitTestCase for EntityUpdate specific and global test
 * This class should extend all EntityUpdate unit tests classes
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate
 */
class EntityUpdateUnitTestCase extends UnitTestCase {

  // The EntityUpdate object
  protected $unit;

  // An entity mock
  protected $entity;

  /**
   *  Global test for all entity update
   */
  public function testUpdate() {
    // All EntityUpdate in production should return TRUE
    // If it returns TRUE the system will register the update as performed
    // and store it in last_entity_update of the entity
    // returning FALSE is only for debugging purposes
    $entity = NULL;
    $this->assertTrue($this->unit->runUpdate($entity));
  }

}