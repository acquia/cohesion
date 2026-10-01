<?php

namespace Drupal\Tests\cohesion_sync\Unit\Config;

use Drupal\cohesion_sync\Config\CohesionPackageStorageBase;
use Drupal\Component\Serialization\Yaml;
use Drupal\Tests\UnitTestCase;

/**
 * Class CohesionPackageStorageBaseTest
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion_sync\Unit\Config
 */
class CohesionPackageStorageBaseTest extends UnitTestCase {

  /**
   * @var \Drupal\cohesion_sync\Config\CohesionPackageStorageBase|\Drupal\cohesion_sync\Config\CohesionPackageStorageBase&\PHPUnit\Framework\MockObject\MockObject|\PHPUnit\Framework\MockObject\MockObject
   */
  private $storage;

  const FIXTURE_PATH = __DIR__ . '/../../../fixtures/cpt_test_component';

  const CONFIGS = [
    'cohesion_elements.cohesion_component.cpt_test_component',
    'cohesion_elements.cohesion_component.cpt_test_component_2',
    'cohesion_elements.cohesion_component_category.cpt_cat_test',
    'views.view.files',
    'views.view.content',
  ];
  const FILES = [
    '99e1ebbf-0ba1-4bf3-92aa-122705f75155' => 'file',
  ];

  protected function setUp(): void {
    parent::setUp();

    $mock = $this->getMockForAbstractClass(CohesionPackageStorageBase::class, [], '', true, true, true, ['listAll', 'exists', 'read'], );
    $mock->expects($this->any())
      ->method('listAll')
      ->will($this->returnValue(self::CONFIGS));
    $mock->expects($this->any())
      ->method('exists')
      ->willReturnCallback(function ($id) {
        return in_array($id, self::CONFIGS);
      });
    $mock->expects($this->any())
      ->method('read')
      ->willReturnCallback(function ($name) {
        return Yaml::decode(file_get_contents(self::FIXTURE_PATH . '/' . $name . '.yml'));
      });
    $this->storage = $mock;
  }

  public function testGetStorageFileList() {
    $this->assertEquals(self::FILES, $this->storage->getStorageFileList());
  }
}
