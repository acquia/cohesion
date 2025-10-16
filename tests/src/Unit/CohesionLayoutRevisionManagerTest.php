<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion\CohesionLayoutRevisionManager;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for CohesionLayoutRevisionManager.
 *
 * @group cohesion
 * @coversDefaultClass \Drupal\cohesion\CohesionLayoutRevisionManager
 */
class CohesionLayoutRevisionManagerTest extends UnitTestCase {

  /**
   * The revision manager.
   *
   * @var \Drupal\cohesion\CohesionLayoutRevisionManager
   */
  protected $revisionManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The logger.
   *
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->database = $this->createMock(Connection::class);
    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);
    $messenger = $this->createMock(MessengerInterface::class);

    $this->revisionManager = new CohesionLayoutRevisionManager(
      $this->database,
      $this->entityTypeManager,
      $this->logger,
      $messenger
    );
  }

  /**
   * @covers ::findOrphanedRevisions
   */
  public function testFindOrphanedRevisions() {
    $manager = $this->getMockBuilder(CohesionLayoutRevisionManager::class)
      ->setConstructorArgs([
        $this->database,
        $this->entityTypeManager,
        $this->logger,
        $this->createMock(MessengerInterface::class),
      ])
      ->onlyMethods(['getAllRevisions', 'getReferencedRevisions', 'filterOutActiveRevisions'])
      ->getMock();

    $manager->method('getAllRevisions')->willReturn([1, 2, 3, 4, 5]);
    $manager->method('getReferencedRevisions')->willReturn([2, 4]);
    $manager->method('filterOutActiveRevisions')->willReturnArgument(0);

    $result = $manager->findOrphanedRevisions();
    $this->assertEquals([1, 3, 5], array_values($result));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisions() {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(FALSE);
    $revision->method('getEntityTypeId')->willReturn('cohesion_layout');

    $storage->method('loadRevision')->willReturn($revision);
    $storage->expects($this->exactly(3))->method('deleteRevision');

    $result = $this->revisionManager->batchDeleteRevisions([1, 2, 3]);
    $this->assertEquals(3, $result);
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsSkipsDefaultRevision() {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(TRUE);
    $revision->method('getEntityTypeId')->willReturn('cohesion_layout');

    $storage->method('loadRevision')->willReturn($revision);
    $storage->expects($this->never())->method('deleteRevision');

    $result = $this->revisionManager->batchDeleteRevisions([1]);
    $this->assertEquals(0, $result);
  }

}
