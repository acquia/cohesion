<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion\CohesionLayoutRevisionManager;
use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Schema;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\State\StateInterface;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;

/**
 * Unit tests for CohesionLayoutRevisionManager.
 *
 * @group Cohesion
 * @coversDefaultClass \Drupal\cohesion\CohesionLayoutRevisionManager
 */
class CohesionLayoutRevisionManagerTest extends UnitTestCase {

  /**
   * The revision manager under test.
   *
   * @var \Drupal\cohesion\CohesionLayoutRevisionManager
   */
  protected CohesionLayoutRevisionManager $revisionManager;

  /**
   * The database connection mock.
   *
   * @var \Drupal\Core\Database\Connection|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $database;

  /**
   * The database schema mock.
   *
   * @var \Drupal\Core\Database\Schema|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $schema;

  /**
   * The entity type manager mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * The logger mock.
   *
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * The messenger mock.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /** @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
  protected $configFactory;

  /** @var \Drupal\Core\Lock\LockBackendInterface|\PHPUnit\Framework\MockObject\MockObject */
  protected $lock;

  /** @var \Drupal\Core\State\StateInterface|\PHPUnit\Framework\MockObject\MockObject */
  protected $stateMock;

  /** @var \Drupal\Component\Datetime\TimeInterface|\PHPUnit\Framework\MockObject\MockObject */
  protected $time;

  /** @var \Drupal\Core\Queue\QueueFactory|\PHPUnit\Framework\MockObject\MockObject */
  protected $queueFactory;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->schema = $this->createMock(Schema::class);
    $this->database = $this->createMock(Connection::class);
    $this->database->method('schema')->willReturn($this->schema);

    $this->entityTypeManager = $this->createMock(EntityTypeManagerInterface::class);
    $this->logger = $this->createMock(LoggerInterface::class);
    $this->messenger = $this->createMock(MessengerInterface::class);
    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->lock = $this->createMock(LockBackendInterface::class);
    $this->stateMock = $this->createMock(StateInterface::class);
    $this->time = $this->createMock(TimeInterface::class);
    $this->queueFactory = $this->createMock(QueueFactory::class);

    $this->revisionManager = new CohesionLayoutRevisionManager(
      $this->database,
      $this->entityTypeManager,
      $this->logger,
      $this->messenger,
      $this->configFactory,
      $this->lock,
      $this->stateMock,
      $this->time,
      $this->queueFactory,
    );
  }

  // ---------------------------------------------------------------------------
  // Helpers
  // ---------------------------------------------------------------------------

  /**
   * Build a partial mock of CohesionLayoutRevisionManager.
   *
   * @param array $methods
   *   Methods to mock.
   *
   * @return \Drupal\cohesion\CohesionLayoutRevisionManager|\PHPUnit\Framework\MockObject\MockObject
   */
  protected function buildManagerMock(array $methods): CohesionLayoutRevisionManager {
    return $this->getMockBuilder(CohesionLayoutRevisionManager::class)
      ->setConstructorArgs([
        $this->database,
        $this->entityTypeManager,
        $this->logger,
        $this->messenger,
        $this->configFactory,
        $this->lock,
        $this->stateMock,
        $this->time,
        $this->queueFactory,
      ])
      ->onlyMethods($methods)
      ->getMock();
  }

  /**
   * Build a fluent SELECT query mock supporting chaining and execute().
   */
  protected function buildSelectMock($fetchColReturn = [], $fetchFieldReturn = 0): SelectInterface {
    $statementMock = $this->getMockBuilder(\stdClass::class)
      ->addMethods(['fetchCol', 'fetchField'])
      ->getMock();
    $statementMock->method('fetchCol')->willReturn($fetchColReturn);
    $statementMock->method('fetchField')->willReturn($fetchFieldReturn);

    $selectMock = $this->createMock(SelectInterface::class);
    $selectMock->method('addField')->willReturnSelf();
    $selectMock->method('distinct')->willReturnSelf();
    $selectMock->method('leftJoin')->willReturnSelf();
    $selectMock->method('isNull')->willReturnSelf();
    $selectMock->method('range')->willReturnSelf();
    $selectMock->method('fields')->willReturnSelf();
    $selectMock->method('condition')->willReturnSelf();
    $selectMock->method('isNotNull')->willReturnSelf();
    $selectMock->method('execute')->willReturn($statementMock);
    // countQuery() returns another select that executes to fetchField().
    $countSelect = $this->createMock(SelectInterface::class);
    $countSelect->method('execute')->willReturn($statementMock);
    $selectMock->method('countQuery')->willReturn($countSelect);

    return $selectMock;
  }

  // ---------------------------------------------------------------------------
  // ::countOrphanedRevisions (SQL COUNT path)
  // ---------------------------------------------------------------------------

  /**
   * @covers ::countOrphanedRevisions
   */
  public function testCountOrphanedRevisionsReturnsZeroWhenTableMissing(): void {
    $this->schema->method('tableExists')->with('cohesion_layout_field_revision')->willReturn(FALSE);

    $this->assertEquals(0, $this->revisionManager->countOrphanedRevisions());
  }

  /**
   * @covers ::countOrphanedRevisions
   */
  public function testCountOrphanedRevisionsReturnsSqlCount(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery', 'getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([]);

    $selectMock = $this->buildSelectMock([], 42);
    $manager->method('buildOrphanQuery')->willReturn($selectMock);

    $this->assertEquals(42, $manager->countOrphanedRevisions());
  }

  /**
   * @covers ::countOrphanedRevisions
   */
  public function testCountOrphanedRevisionsCatchesException(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery']);
    $manager->method('buildOrphanQuery')->willThrowException(new \RuntimeException('DB error'));

    $this->logger->expects($this->once())->method('error');
    $this->assertEquals(0, $manager->countOrphanedRevisions());
  }

  // ---------------------------------------------------------------------------
  // ::findOrphanedRevisionsBatch (new SQL LIMIT path)
  // ---------------------------------------------------------------------------

  /**
   * @covers ::findOrphanedRevisionsBatch
   */
  public function testFindOrphanedRevisionsBatchReturnsEmptyWhenTableMissing(): void {
    $this->schema->method('tableExists')->with('cohesion_layout_field_revision')->willReturn(FALSE);

    $this->assertEquals([], $this->revisionManager->findOrphanedRevisionsBatch(100));
  }

  /**
   * Data provider for invalid $limit values.
   */
  public static function provideInvalidLimits(): array {
    return [
      'zero'     => [0],
      'negative' => [-10],
    ];
  }

  /**
   * @covers ::findOrphanedRevisionsBatch
   * @dataProvider provideInvalidLimits
   */
  public function testFindOrphanedRevisionsBatchReturnsEmptyForInvalidLimit(int $limit): void {
    $db = $this->createMock(Connection::class);
    $db->expects($this->never())->method('schema');
    $manager = new CohesionLayoutRevisionManager(
      $db,
      $this->entityTypeManager,
      $this->logger,
      $this->messenger,
      $this->configFactory,
      $this->lock,
      $this->stateMock,
      $this->time,
      $this->queueFactory,
    );
    $this->assertSame([], $manager->findOrphanedRevisionsBatch($limit));
  }

  /**
   * @covers ::findOrphanedRevisionsBatch
   */
  public function testFindOrphanedRevisionsBatchReturnsCastIntegers(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery', 'getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([]);

    // Simulate DB returning string IDs (as MySQL drivers often do).
    $selectMock = $this->buildSelectMock(['7', '14', '21'], 0);
    $manager->method('buildOrphanQuery')->willReturn($selectMock);

    $result = $manager->findOrphanedRevisionsBatch(3);
    $this->assertSame([7, 14, 21], $result);
  }

  /**
   * @covers ::findOrphanedRevisionsBatch
   */
  public function testFindOrphanedRevisionsBatchAppliesLimit(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery', 'getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([]);

    $selectMock = $this->buildSelectMock([1, 2], 0);
    // Verify range(0, 50) is called with our limit.
    $selectMock->expects($this->once())->method('range')->with(0, 50)->willReturnSelf();
    $manager->method('buildOrphanQuery')->willReturn($selectMock);

    $manager->findOrphanedRevisionsBatch(50);
  }

  /**
   * @covers ::findOrphanedRevisionsBatch
   */
  public function testFindOrphanedRevisionsBatchCatchesException(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery']);
    $manager->method('buildOrphanQuery')->willThrowException(new \RuntimeException('DB error'));

    $this->logger->expects($this->once())->method('error');
    $this->assertEquals([], $manager->findOrphanedRevisionsBatch(100));
  }

  // ---------------------------------------------------------------------------
  // ::buildOrphanQuery (tested via public countOrphanedRevisions)
  // ---------------------------------------------------------------------------

  /**
   * @covers ::buildOrphanQuery
   * @covers ::countOrphanedRevisions
   */
  public function testBuildOrphanQueryJoinsReferenceTablesFromCache(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([
      ['table' => 'node__field_canvas', 'field' => 'field_canvas_target_revision_id'],
      ['table' => 'node_revision__field_canvas', 'field' => 'field_canvas_target_revision_id'],
    ]);

    $selectMock = $this->buildSelectMock([], 0);
    // 3 leftJoins: cohesion_layout + 2 reference tables.
    $selectMock->expects($this->exactly(3))->method('leftJoin')->willReturnSelf();
    $this->database->method('select')->willReturn($selectMock);

    $manager->countOrphanedRevisions();
  }

  /**
   * @covers ::buildOrphanQuery
   * @covers ::countOrphanedRevisions
   */
  public function testBuildOrphanQueryWithNoReferenceTablesJoinsOnlyLayout(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([]);

    $selectMock = $this->buildSelectMock([], 0);
    // Only 1 leftJoin: cohesion_layout.
    $selectMock->expects($this->exactly(1))->method('leftJoin')->willReturnSelf();
    $this->database->method('select')->willReturn($selectMock);

    $manager->countOrphanedRevisions();
  }

  // ---------------------------------------------------------------------------
  // ::batchDeleteRevisions
  // ---------------------------------------------------------------------------

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsDeletesAll(): void {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(FALSE);
    $revision->method('getEntityTypeId')->willReturn('cohesion_layout');

    $storage->method('loadRevision')->willReturn($revision);
    $storage->expects($this->exactly(3))->method('deleteRevision');

    $this->assertEquals(3, $this->revisionManager->batchDeleteRevisions([1, 2, 3]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsSkipsDefaultRevision(): void {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(TRUE);
    $revision->method('getEntityTypeId')->willReturn('cohesion_layout');

    $storage->method('loadRevision')->willReturn($revision);
    $storage->expects($this->never())->method('deleteRevision');

    $this->assertEquals(0, $this->revisionManager->batchDeleteRevisions([1]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsSkipsWrongEntityType(): void {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(FALSE);
    $revision->method('getEntityTypeId')->willReturn('node');

    $storage->method('loadRevision')->willReturn($revision);
    $storage->expects($this->never())->method('deleteRevision');

    // warning() fires once per skipped revision + once for the batch summary.
    $this->logger->expects($this->exactly(2))->method('warning');
    $this->assertEquals(0, $this->revisionManager->batchDeleteRevisions([99]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsSkipsNotFoundRevision(): void {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);
    $storage->method('loadRevision')->willReturn(NULL);
    $storage->expects($this->never())->method('deleteRevision');

    $this->assertEquals(0, $this->revisionManager->batchDeleteRevisions([5]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsReturnsZeroForEmptyInput(): void {
    $this->assertEquals(0, $this->revisionManager->batchDeleteRevisions([]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsReturnsZeroWhenStorageUnavailable(): void {
    $nonRevisionable = $this->createMock(EntityStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($nonRevisionable);

    $this->logger->expects($this->once())->method('error');
    $this->assertEquals(0, $this->revisionManager->batchDeleteRevisions([1, 2, 3]));
  }

  /**
   * @covers ::batchDeleteRevisions
   *
   * Tests both that storage is never reached AND that a warning is logged.
   */
  public function testBatchDeleteRevisionsHandlesAllInvalidIds(): void {
    $this->entityTypeManager->expects($this->never())->method('getStorage');
    $this->logger->expects($this->once())->method('warning');
    $this->assertEquals(0, $this->revisionManager->batchDeleteRevisions(['abc', 0, -1]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsDeduplicatesIds(): void {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(FALSE);
    $revision->method('getEntityTypeId')->willReturn('cohesion_layout');

    $storage->method('loadRevision')->willReturn($revision);
    // [5, 5, 5] deduplicates to [5] — deleteRevision called exactly once.
    $storage->expects($this->exactly(1))->method('deleteRevision');

    $this->assertEquals(1, $this->revisionManager->batchDeleteRevisions([5, 5, 5]));
  }

  /**
   * @covers ::batchDeleteRevisions
   */
  public function testBatchDeleteRevisionsContinuesAfterDeleteException(): void {
    $storage = $this->createMock(RevisionableStorageInterface::class);
    $this->entityTypeManager->method('getStorage')->willReturn($storage);

    $revision = $this->createMock(RevisionableInterface::class);
    $revision->method('isDefaultRevision')->willReturn(FALSE);
    $revision->method('getEntityTypeId')->willReturn('cohesion_layout');

    $storage->method('loadRevision')->willReturn($revision);
    $storage->method('deleteRevision')
      ->willReturnCallback(function ($id) {
        if ($id === 1) {
          throw new \RuntimeException('Lock timeout');
        }
      });

    $this->logger->expects($this->atLeastOnce())->method('error');
    $this->assertSame(1, $this->revisionManager->batchDeleteRevisions([1, 2]));
  }

  // ---------------------------------------------------------------------------
  // ::processCronCleanup
  // ---------------------------------------------------------------------------

  /**
   * @covers ::processCronCleanup
   */
  public function testProcessCronCleanupReturnsDisabledWhenConfigOff(): void {
    $config = $this->getMockBuilder(\stdClass::class)
      ->addMethods(['get'])
      ->getMock();
    $config->method('get')->with('delete_orphaned_revisions_on_cron')->willReturn(FALSE);
    $this->configFactory->method('get')->with('cohesion.settings')->willReturn($config);

    // Lock must never be acquired when config is off.
    $this->lock->expects($this->never())->method('acquire');

    $this->assertSame(['status' => 'disabled'], $this->revisionManager->processCronCleanup());
  }

  // ---------------------------------------------------------------------------
  // Integration
  // ---------------------------------------------------------------------------

  /**
   * @covers ::countOrphanedRevisions
   */
  public function testCountOrphanedRevisionsCastsSqlResultToInt(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery', 'getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([]);
    // DB returns a string — common with MySQL PDO drivers.
    $manager->method('buildOrphanQuery')->willReturn($this->buildSelectMock([], '42'));

    $this->assertSame(42, $manager->countOrphanedRevisions());
  }

  /**
   * @covers ::findOrphanedRevisionsBatch
   */
  public function testFindOrphanedRevisionsBatchCastsResultsToInt(): void {
    $this->schema->method('tableExists')->willReturn(TRUE);

    $manager = $this->buildManagerMock(['buildOrphanQuery', 'getCohesionReferenceTables']);
    $manager->method('getCohesionReferenceTables')->willReturn([]);
    // DB returns string IDs — must be cast to int.
    $manager->method('buildOrphanQuery')->willReturn($this->buildSelectMock(['10', '20', '30']));

    $this->assertSame([10, 20, 30], $manager->findOrphanedRevisionsBatch(100));
  }

  /**
   * @covers ::countOrphanedRevisions
   * @covers ::findOrphanedRevisionsBatch
   */
  public function testMissingTableReturnsEarlyWithoutQuery(): void {
    $this->schema->method('tableExists')->with('cohesion_layout_field_revision')->willReturn(FALSE);
    $this->database->expects($this->never())->method('select');

    $this->assertSame(0, $this->revisionManager->countOrphanedRevisions());
    $this->assertSame([], $this->revisionManager->findOrphanedRevisionsBatch(100));
  }

}
