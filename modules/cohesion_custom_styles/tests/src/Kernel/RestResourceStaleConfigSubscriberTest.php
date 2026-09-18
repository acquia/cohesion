<?php

namespace Drupal\Tests\cohesion_custom_styles\Kernel;

use Drupal\Core\Config\StorageTransformEvent;
use Drupal\Core\Config\ConfigEvents;
use Drupal\cohesion_custom_styles\EventSubscriber\RestResourceStaleConfigSubscriber;
use Drupal\KernelTests\KernelTestBase;

/**
 * Tests RestResourceStaleConfigSubscriber.
 *
 * @group Cohesion
 * @covers \Drupal\cohesion_custom_styles\EventSubscriber\RestResourceStaleConfigSubscriber
 */
class RestResourceStaleConfigSubscriberTest extends KernelTestBase {

  protected static $modules = [
    'system',
    'user',
    'file',
    'serialization',
    'rest',
    'cohesion',
    'cohesion_elements',
    'cohesion_custom_styles',
  ];

  /**
   * Config names the subscriber should strip from the import storage.
   *
   * @var string[]
   */
  protected const RESOURCES_TO_REMOVE = [
    'rest.resource.cohesion_custom_styles',
    'rest.resource.cohesion_custom_style_type',
    'rest.resource.dx8_resource',
  ];

  protected function setUp(): void {
    parent::setUp();
    $this->installSchema('cohesion', ['coh_usage']);
  }

  /**
   * Asserts stale rest.resource.* entries are removed from the storage.
   */
  public function testStripsStaleRestResourceConfig() {
    $storage = $this->container->get('config.storage');

    foreach (self::RESOURCES_TO_REMOVE as $name) {
      $storage->write($name, ['id' => str_replace('rest.resource.', '', $name)]);
    }
    $storage->write('rest.resource.unrelated', ['id' => 'unrelated']);

    $event = new StorageTransformEvent($storage);
    (new RestResourceStaleConfigSubscriber())->onStorageTransformImport($event);

    foreach (self::RESOURCES_TO_REMOVE as $name) {
      $this->assertFalse($storage->exists($name), "$name should have been stripped from the import storage.");
    }
    $this->assertTrue(
      $storage->exists('rest.resource.unrelated'),
      'Unrelated rest resource config should not be touched.'
    );
  }

  /**
   * Asserts the subscriber is a no-op when the config is already absent.
   */
  public function testNoOpWhenConfigAlreadyAbsent() {
    $storage = $this->container->get('config.storage');

    $event = new StorageTransformEvent($storage);
    (new RestResourceStaleConfigSubscriber())->onStorageTransformImport($event);

    foreach (self::RESOURCES_TO_REMOVE as $name) {
      $this->assertFalse($storage->exists($name));
    }
  }

  /**
   * Asserts the subscriber is registered on the correct core event.
   */
  public function testIsSubscribedToStorageTransformImport() {
    $events = RestResourceStaleConfigSubscriber::getSubscribedEvents();
    $this->assertArrayHasKey(ConfigEvents::STORAGE_TRANSFORM_IMPORT, $events);
  }

}
