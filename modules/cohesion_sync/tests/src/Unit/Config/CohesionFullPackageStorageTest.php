<?php

namespace Drupal\Tests\cohesion_sync\Unit\Config;

use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\Config\CohesionFullPackageStorage;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Class CohesionFullPackageStorageTest
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion_sync\Unit\Config
 */
class CohesionFullPackageStorageTest extends UnitTestCase {

  /**
   * The main drupal config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  public $configStorage;

  /**
   * The config manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  public $configManager;

  /**
   * The cohesion full package storage.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionFullPackageStorage
   */
  public $storage;

  /**
   * The configs stored in DB.
   *
   * @var array[]
   */
  public $configs;

  /**
   * The usage plugin manager service.
   *
   * @var \Drupal\cohesion\UsagePluginManager|\PHPUnit\Framework\MockObject\MockObject
   */
  public $usagePluginManager;



  /**
   * @inheritDoc
   */
  public function setUp(): void {
    parent::setUp();
    $this->configStorage = $this->getMockBuilder(StorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->configManager = $this->getMockBuilder(ConfigManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->usagePluginManager = $this->getMockBuilder(UsagePluginManager::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->configs = [
      'some_module.some_config.config_id' => [
        'entity_type' => 'some_config',
        'exists' => TRUE,
        'data' => ['some' => 'data'],
      ],
      'cohesion_templates.cohesion_templates.config_id' => [
        'entity_type' => 'cohesion_templates',
        'exists' => TRUE,
        'data' => ['some' => 'data'],
      ],
      'cohesion_elements.cohesion_component.config_id' => [
        'entity_type' => 'cohesion_component',
        'exists' => TRUE,
        'data' => ['some' => 'data'],
      ],
      'some_mode.config.does_not_exists' => [
        'entity_type' => 'cohesion_component',
        'exists' => FALSE,
        'data' => ['some' => 'data'],
      ],
      'cohesion.sync.settings' => [
        'entity_type' => FALSE,
        'exists' => TRUE,
        'data' => [
          'enabled_entity_types' => [
            'cohesion_component' => 1,
            'cohesion_templates' => 0,
          ],
        ],
      ],
    ];

    $this->configStorage->expects($this->any())
      ->method('exists')
      ->willReturnCallback(function ($name) {
        return $this->configs[$name]['exists'] ?? TRUE;
      });

    $this->configStorage->expects($this->any())
      ->method('read')
      ->willReturnCallback(function ($name) {
        return $this->configs[$name]['data'] ?? FALSE;
      });

    $this->configManager->expects($this->any())
      ->method('getEntityTypeIdByName')
      ->willReturnCallback(function ($name) {
        return $this->configs[$name]['entity_type'] ?? NULL;
      });

    $this->usagePluginManager->expects($this->any())
      ->method('getEnabledEntityTypes')
      ->willReturnCallback(function () {
        return $this->configStorage->read('cohesion.sync.settings') ? $this->configStorage->read('cohesion.sync.settings')['enabled_entity_types'] : [];
      });
  }

  /**
   * Test the listAll methods.
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::listAll
   */
  public function testListAll() {
    $this->configStorage->expects($this->any())
      ->method('listAll')
      ->willReturn(array_keys($this->configs));
    $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);

    $this->assertEquals($this->storage->listAll(), ['cohesion_elements.cohesion_component.config_id'], 'ListAll show only contain the cohesion component');
    $this->assertEquals($this->storage->listAll('some_prefix'), ['cohesion_elements.cohesion_component.config_id'], 'ListAll show only contain the cohesion component regardless of the prefix');
  }

  /**
   * Test the read metthod.
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::read
   */
  public function testRead() {
    $this->configStorage->expects($this->any())
      ->method('listAll')
      ->willReturn(array_keys($this->configs));
    $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);

    $this->assertFalse($this->storage->read('some_module.some_config.config_id'), 'Reading normal config should return FALSE');
    $this->assertFalse($this->storage->read('cohesion_templates.cohesion_templates.config_id'), 'Reading config not included in the include list settings should return FALSE');
    $this->assertEquals($this->storage->read('cohesion_elements.cohesion_component.config_id'), $this->configs['cohesion_elements.cohesion_component.config_id']['data'], 'Reading config included in the include list should return the data');
  }

  /**
   * Test the exists metthod.
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::exists
   */
  public function testExists() {
    $this->configStorage->expects($this->any())
      ->method('listAll')
      ->willReturn(array_keys($this->configs));
    $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);

    $this->assertTrue($this->storage->exists('some_module.some_config.config_id'), 'Normal config should exsist');
    $this->assertTrue($this->storage->exists('cohesion_templates.cohesion_templates.config_id'), 'Reading normal config should not exsist');
    $this->assertFalse($this->storage->exists('some_mode.config.does_not_exists'), 'Config that does not exists in the main config storage should not exists in cohesion storage');
  }

  /**
   * Test the buildDependencies method.
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::buildDependencies
   */
  public function testBuildDependencies() {
    $this->configs['some_module.config.with_dependencies'] =  [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => [
        'some' => 'data',
        'dependencies' => [
          'config' => [
            'config_dependency_1',
            'config_dependency_2',
          ],
          'module' => [
            'module_dependency_1',
          ],
          'content' => [
            'file:file:dependency_1',
          ],
        ],
      ],
    ];

    $this->configs['config_dependency_1'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => TRUE,
    ];

    $this->configs['config_dependency_2'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => TRUE,
    ];

    $this->configStorage->expects($this->any())
      ->method('listAll')
      ->willReturn(array_keys($this->configs));
    $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);

    $this->assertContains('config_dependency_1', $this->storage->listAll(), 'List all should contain config dependency 1');
    $this->assertContains('config_dependency_2', $this->storage->listAll(), 'List all should contain config dependency 2');
    $this->assertNotContains('module_dependency_1', $this->storage->listAll(), 'List all should not contain module dependency');
    $this->assertNotContains('file:file:dependency_1', $this->storage->listAll(), 'List all should not contain content dependency');
  }

  /**
   * Test the configStatus method.
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::configStatus
   */
  public function testConfigStatus() {
    $this->configs['some_module.config.without_status'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => [
        'some' => 'data',
      ],
    ];

    $this->configs['some_module.config.with_status_true'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => ['some' => 'data', 'status' => TRUE],
    ];

    $this->configs['some_module.config.with_status_false'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => ['some' => 'data', 'status' => FALSE],
    ];

    $this->configStorage->expects($this->any())
      ->method('listAll')
      ->willReturn(array_keys($this->configs));
    $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);

    $this->assertContains('some_module.config.without_status', $this->storage->listAll(), 'List all should contain config with no status');
    $this->assertContains('some_module.config.with_status_true', $this->storage->listAll(), 'List all should contain config with status TRUE');
    $this->assertNotContains('some_module.config.with_status_false', $this->storage->listAll(), 'List all should not contain config with status FALSE');
  }

  /**
   * Test the getStorageFileList and buildStorageFileList
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::getStorageFileList
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::buildStorageFileList
   */
  public function testGetStorageFileList() {
    $this->configs['config_with.file.dependency'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => [
        'dependencies' => [
          'config' => [
            'config_dependency_1_with_file',
          ],
          'module' => [
            'module_dependency_1',
          ],
          'content' => [
            'file:file:dependency_1',
            'file:file:dependency_2',
            'not:file:dependency',
          ],
        ],
      ],
    ];

    $this->configs['config_with.file.dependency_2'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => [
        'status' => TRUE,
        'dependencies' => [
          'content' => [
            'file:file:dependency_4',
          ],
        ],
      ],
    ];

    $this->configs['config_with.file.dependency_3'] = [
      'entity_type' => 'cohesion_component',
      'exists' => TRUE,
      'data' => [
        'dependencies' => [
          'content' => [
            'file:file:dependency_5',
          ],
        ],
      ],
    ];

    $this->configStorage->expects($this->any())
      ->method('listAll')
      ->willReturn(array_keys($this->configs));
    $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);

    $excepted = [
      'dependency_1' => 'file',
      'dependency_2' => 'file',
      'dependency_5' => 'file',
      'dependency_4' => 'file',
    ];
    $this->assertEquals($excepted, $this->storage->getStorageFileList(), 'getStorageFileList should contain file dependency');


  }

  /**
   * Test the getIncludedEntityTypes method.
   *
   * @dataProvider dataGetIncludedEntityTypes
   *
   * @param array|null $config
   *  The drupal config.
   * @param  array|null $results
   *
   * @covers \Drupal\cohesion_sync\Config\CohesionFullPackageStorage::getIncludedEntityTypes
   */
  public function testGetIncludedEntityTypes($config, $results) {

    if ($config == NULL) {
      $this->configs = [];
      $this->configStorage->expects($this->any())
        ->method('listAll')
        ->willReturn(array_keys($this->configs));
      $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);
      $this->expectException(\Exception::class);
      $this->storage->getIncludedEntityTypes();
    }
    else {
      $this->configs = $config;
      $this->configStorage->expects($this->any())
        ->method('listAll')
        ->willReturn(array_keys($this->configs));
      $this->storage = new CohesionFullPackageStorage($this->configStorage, $this->configManager, $this->usagePluginManager);
      $this->assertEquals($results, $this->storage->getIncludedEntityTypes());
    }
  }

  /**
   * Data provider for ::testGetIncludedEntityTypes.
   * @return array
   */
  public function dataGetIncludedEntityTypes() {

    $items = [];

    $config = NUll;
    $result = NULL;

    $items[] = [
      $config,
      $result,
    ];

    // With enabled entity.
    $config = [
      'cohesion.sync.settings' => [
        'data' => [
          'enabled_entity_types' => [
            'cohesion_component' => 1,
            'another_type' => 'enabled',
            'cohesion_templates' => 0,
          ],
        ],
      ],
    ];
    $result = [
      'cohesion_component',
      'another_type',
    ];

    $items[] = [
      $config,
      $result,
    ];

    // With no enabled entity.
    $config = [
      'cohesion.sync.settings' => [
        'data' => [
          'enabled_entity_types' => [
            'cohesion_component' => 0,
            'another_type' => 0,
            'cohesion_templates' => 0,
          ],
        ],
      ],
    ];
    $result = [];

    $items[] = [
      $config,
      $result,
    ];

    return $items;
  }
}
