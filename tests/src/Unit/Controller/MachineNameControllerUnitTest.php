<?php

namespace Drupal\Tests\cohesion\Unit\Controller;

use Drupal\cohesion\Controller\MachineNameController;
use Drupal\Component\Transliteration\TransliterationInterface;
use Drupal\Core\Access\CsrfTokenGenerator;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class MachineNameStorageMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Controller
 */
class MachineNameStorageMock {
  protected $entity_ids;

  public function __construct(array $entity_ids) {
    $this->entity_ids = $entity_ids;
  }

  public function load($id) {
    return in_array($id, $this->entity_ids);
  }

}

/**
 * Class MachineNameControllerMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Controller
 */
class MachineNameControllerMock extends MachineNameController {

  public function setStorage($storage) {
    $this->storage = $storage;
  }

}

/**
 * @group Cohesion
 */
class MachineNameControllerUnitTest extends UnitTestCase {

  protected $mockUnit;

  public function setUp(): void {
    // Create a mock of the classes required to init MachineNameController.
    $prophecy = $this->prophesize(TransliterationInterface::CLASS);
    $transliteration_interface = $prophecy->reveal();

    $prophecy = $this->prophesize(CsrfTokenGenerator::CLASS);
    $csrf = $prophecy->reveal();

    $prophecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $entity_type_manager = $prophecy->reveal();

    $this->mockUnit = new MachineNameControllerMock($transliteration_interface, $csrf, $entity_type_manager);
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityDoesNotExistNoTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([]));

    $input = 'very_short';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 24;

    $this->assertEquals('very_short', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityDoesNotExistTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([]));

    $input = 'this_string_is_way_longer_than_the_maxlength';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 24;

    $this->assertEquals('this_string_is_way_longe', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityExistsNoTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([
      'existing_entity',
    ]));

    $input = 'existing_entity';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 24;

    $this->assertEquals('existing_entity_0', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityExistsMultipleNoTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([
      'existing_entity',
      'existing_entity_0',
      'existing_entity_1',
      'existing_entity_2',
    ]));

    $input = 'existing_entity';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 24;

    $this->assertEquals('existing_entity_3', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityExistsTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([
      'this_entit',
    ]));

    $input = 'this_entity_already_exists';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 10;

    $this->assertEquals('this_ent_0', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityExistsMultipleTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([
      'this_entit',
      'this_ent_0',
      'this_ent_1',
      'this_ent_2',
      'this_ent_3',
      'this_ent_4',
      'this_ent_5',
      'this_ent_6',
      'this_ent_7',
      'this_ent_8',
      'this_ent_9',
    ]));

    $input = 'this_entity_already_exists';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 10;

    $this->assertEquals('this_en_10', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityNumericSuffixButDoesNotExist() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([]));

    $input = 'this_ent_3';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 10;

    $this->assertEquals('this_ent_3', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityNumericSuffixButSeriesDoesNotExist() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([
      'this_ent_3',
    ]));

    $input = 'this_ent_3';
    $field_prefix = '';
    $entity_id = '';
    $maxlength = 10;

    $this->assertEquals('this_ent_0', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

  /**
   * @covers \Drupal\cohesion\Controller\MachineNameController::getUniqueEntityId
   */
  public function testEntityMatchesThisEntityNoTruncation() {
    $this->mockUnit->setStorage(new MachineNameStorageMock([
      'very_short',
    ]));

    $input = 'very_short';
    $field_prefix = '';
    $entity_id = 'very_short';
    $maxlength = 24;

    $this->assertEquals('very_short', $this->mockUnit->getUniqueEntityId($input, $field_prefix, $entity_id, $maxlength));
  }

}
