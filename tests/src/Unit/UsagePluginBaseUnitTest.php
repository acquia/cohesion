<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Class MockUsageEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class MockUsageEntity {
  protected $id;

  public function __construct($id) {
    $this->id = $id;
  }

  public function uuid() {
    return 'uuid=' . $this->id;
  }

  public function getFileUri() {
    return $this->id;
  }

}


/**
 * This abstract is used by other tests.
 *
 * @group Cohesion
 */
abstract class UsagePluginBaseUnitTest extends UnitTestCase {

  /**
   * @var \Drupal\cohesion\UsagePluginInterface
   */
  protected $unit;

  protected $configuration;
  protected $plugin_id;
  protected $plugin_definition;
  protected $entity_type_manager_mock;
  protected $stream_wrapper_manager_mock;
  protected $database_connection_mock;
  protected $theme_handler_mock;

  /**
   * Create mocks of the objects that the plugin needs.
   */
  public function setUp(): void {
    // Mock config.
    $this->configuration = [];
    $this->plugin_id = 'mockup_plugin_id';
    $this->plugin_definition = [
      'name' => 'Mock',
      'entity_type' => 'mock_entity',
    ];

    // Mock service.
    $prophecy = $this->prophesize(EntityStorageInterface::CLASS);
    // Mock function call.
    $prophecy->load(Argument::type('string'))->will(function ($args) {
      // Just return the ID of the entity sent to ->load()
      return new MockUsageEntity($args[0]);
    });
    $prophecy->loadByProperties(Argument::type('array'))->will(function ($args) {
      $key = key($args[0]);
      // Just return the whatever key and value of the entity sent to ->load()
      return [
        new MockUsageEntity($key . '-' . $args[0][$key]),
      ];
    });
    // Mock function call.
    $prophecy->getQuery(Argument::type('string'))->will(function ($args) {
      // Just return the ID of the entity sent to ->load()
      return new class {
        protected $entities = [];

        public function condition($where, $is) {
          if ($where == 'class_name') {
            $this->entities[] = $is;
            return $this;
          }

          if ($where == 'default') {
            $this->entities[] = 'default';
            return $this;
          }
        }

        public function execute() {
          return $this->entities;
        }

        public function accessCheck() {
          return $this;
        }

      };
    });

    // Mock function call.
    $prophecy->loadMultiple(Argument::type('array'))->will(function ($args) {
      return array_map(function ($id) {
        return new MockUsageEntity($id[0]);
      }, $args[0]);
    });
    $storage_manager_mock = $prophecy->reveal();

    // Mock service.
    $prophecy = $this->prophesize(EntityTypeManagerInterface::CLASS);
    $prophecy->getStorage($this->plugin_definition['entity_type'])->willReturn($storage_manager_mock);
    $this->entity_type_manager_mock = $prophecy->reveal();

    // Mock service.
    $prophecy = $this->prophesize(StreamWrapperManager::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    $prophecy->getWrappers(StreamWrapperInterface::LOCAL)->willReturn([]);
    $prophecy->getViaUri(Argument::type('string'))->will(function ($args) {
      // Just return the ID of the entity sent to ->load()
      return new class ($args[0]) {
        protected $id;

        public function __construct($id) {
          $this->id = $id;
        }

        public function getDirectoryPath() {
          return 'directory/' . $this->id . '/';
        }

      };
    });
    $this->stream_wrapper_manager_mock = $prophecy->reveal();

    // Mock service.
    $prophecy = $this->prophesize(Connection::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    $this->database_connection_mock = $prophecy->reveal();

    // Mock service.
    $prophecy = $this->prophesize(ThemeHandlerInterface::CLASS);
    // $prophecy->generate()->willReturn('0000-0000-0000-0000');
    $this->theme_handler_mock = $prophecy->reveal();

  }

  /**
   * Once test method has finished running, whether it succeeded or failed, tearDown() will be invoked.
   * Unset the $unit object.
   */
  public function tearDown(): void {
    unset($this->unit);
  }

}
