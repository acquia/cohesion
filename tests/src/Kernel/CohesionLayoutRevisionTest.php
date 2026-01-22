<?php

namespace Drupal\Tests\cohesion\Kernel;

use Drupal\KernelTests\KernelTestBase;

/**
 * Kernel tests for cohesion orphan cleanup infrastructure.
 *
 * @group cohesion
 */
class CohesionLayoutRevisionTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'user',
    'field',
    'file',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('user');
    $this->installConfig(['system']);
  }

  /**
   * Test basic kernel environment.
   */
  public function testKernelEnvironment() {
    $this->assertInstanceOf('\Drupal\Core\Database\Connection', $this->container->get('database'));
    $this->assertInstanceOf('\Drupal\Core\Entity\EntityTypeManagerInterface', $this->container->get('entity_type.manager'));
  }

  /**
   * Test state system for cleanup tracking.
   */
  public function testStateSystem() {
    $state = $this->container->get('state');
    $state->set('test_orphan_cleanup', 12345);
    $this->assertEquals(12345, $state->get('test_orphan_cleanup', 0));
  }

  /**
   * Test queue system for orphan cleanup.
   */
  public function testQueueSystem() {
    $queue = $this->container->get('queue')->get('test_orphan_queue');
    $this->assertEquals(0, $queue->numberOfItems());

    $queue->createItem(['revision_ids' => [1, 2, 3]]);
    $this->assertEquals(1, $queue->numberOfItems());

    $item = $queue->claimItem();
    $this->assertNotFalse($item);
    $this->assertEquals([1, 2, 3], $item->data['revision_ids']);

    $queue->deleteItem($item);
    $this->assertEquals(0, $queue->numberOfItems());
  }

}
