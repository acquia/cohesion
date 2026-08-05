<?php

namespace Drupal\cohesion;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Database\Connection;
use Drupal\Core\Database\Query\SelectInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Queue\QueueFactory;
use Drupal\Core\State\StateInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for managing cohesion layout revisions cleanup.
 */
class CohesionLayoutRevisionManager {

  /**
   * Maximum number of per-revision detail strings captured in the skip log.
   *
   * Caps $error_details to prevent unbounded memory growth when a large batch
   * has many skipped entries. The total skip count is always logged separately.
   */
  protected const MAX_ERROR_LOG_DETAILS = 20;

  /**
   * Cache for reference tables; NULL means not yet populated.
   *
   * Using NULL as the uninitialised sentinel means an empty array result
   * (no reference tables found) is also cached correctly.
   *
   * @var array|null
   */
  protected ?array $referenceTablesCache = NULL;

  /**
   * Constructs a CohesionLayoutRevisionManager object.
   */
  public function __construct(
    protected Connection $database,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerInterface $logger,
    protected MessengerInterface $messenger,
    protected ConfigFactoryInterface $configFactory,
    protected LockBackendInterface $lock,
    protected StateInterface $state,
    protected TimeInterface $time,
    protected QueueFactory $queueFactory,
  ) {
  }

  /**
   * Count orphaned revisions via SQL COUNT — no PHP memory impact.
   *
   * @return int
   */
  public function countOrphanedRevisions(): int {
    try {
      if (!$this->database->schema()->tableExists('cohesion_layout_field_revision')) {
        return 0;
      }
      return (int) $this->buildOrphanQuery()->countQuery()->execute()->fetchField();
    }
    catch (\Throwable $e) {
      $this->logger->error('Error counting orphaned revisions: @error', ['@error' => $e->getMessage()]);
      return 0;
    }
  }

  /**
   * Find up to $limit orphaned revisions via SQL — no full table load.
   *
   * Callers delete returned IDs before calling again, so no offset is needed.
   *
   * @param int $limit
   *
   * @return int[]
   */
  public function findOrphanedRevisionsBatch(int $limit): array {
    if ($limit <= 0) {
      return [];
    }
    try {
      if (!$this->database->schema()->tableExists('cohesion_layout_field_revision')) {
        return [];
      }
      $query = $this->buildOrphanQuery();
      $query->range(0, $limit);
      return array_map('intval', $query->execute()->fetchCol());
    }
    catch (\Throwable $e) {
      $this->logger->error('Error finding orphaned revisions batch: @error', ['@error' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Build the orphan detection SELECT query using LEFT JOINs.
   *
   * Shared by countOrphanedRevisions() and findOrphanedRevisionsBatch().
   *
   * @return \Drupal\Core\Database\Query\SelectInterface
   */
  protected function buildOrphanQuery(): SelectInterface {
    $query = $this->database->select('cohesion_layout_field_revision', 'clfr');
    $query->addField('clfr', 'revision');
    $query->distinct();
    $query->leftJoin('cohesion_layout', 'cl', 'cl.revision = clfr.revision');
    $query->isNull('cl.revision');

    // Table/field names come from field_storage_config, not user input.
    // array_values() ensures sequential keys for alias generation.
    foreach (array_values($this->getCohesionReferenceTables()) as $i => $table_info) {
      $alias = 'ref' . $i;
      $join_condition = "{$alias}.{$table_info['field']} = clfr.revision";
      $query->leftJoin($table_info['table'], $alias, $join_condition);
      $query->isNull("{$alias}.{$table_info['field']}");
    }

    return $query;
  }

  /**
   * Get cohesion reference tables for ALL entity types.
   *
   * @return array
   *   Array of reference table information.
   */
  protected function getCohesionReferenceTables(): array {
    if ($this->referenceTablesCache !== NULL) {
      return $this->referenceTablesCache;
    }

    $reference_tables = [];

    try {
      $field_storage_configs = $this->entityTypeManager
        ->getStorage('field_storage_config')
        ->loadByProperties(['type' => 'cohesion_entity_reference_revisions']);

      foreach ($field_storage_configs as $field_storage) {
        $settings = $field_storage->getSettings();
        if (isset($settings['target_type']) && $settings['target_type'] === 'cohesion_layout') {
          $field_name = $field_storage->getName();
          $entity_type = $field_storage->getTargetEntityTypeId();
          $target_field = "{$field_name}_target_revision_id";

          foreach (['', '_revision'] as $suffix) {
            $table = "{$entity_type}{$suffix}__{$field_name}";
            if ($this->database->schema()->tableExists($table)) {
              $reference_tables[] = [
                'table' => $table,
                'field' => $target_field,
              ];
            }
          }
        }
      }
    }
    catch (\Throwable $e) {
      $this->logger->error('Error getting reference tables: @error', ['@error' => $e->getMessage()]);
      // Do not cache on error — return empty without persisting so the
      // next call retries, preventing stale empty cache from causing
      // non-orphaned revisions to be treated as deletable.
      return [];
    }

    $this->referenceTablesCache = $reference_tables;
    return $reference_tables;
  }

  /**
   * Delete cohesion layout revisions in batches.
   *
   * @param array $revision_ids
   *   Array of revision IDs to delete.
   * @param string $context
   *   Context for logging.
   *
   * @return int
   *   Number of revisions successfully deleted.
   */
  public function batchDeleteRevisions(array $revision_ids, string $context = ''): int {
    if (empty($revision_ids)) {
      return 0;
    }

    // Normalise to positive integers and deduplicate.
    $valid_ids = [];
    foreach ($revision_ids as $id) {
      if (is_numeric($id) && (int) $id > 0) {
        $valid_ids[] = (int) $id;
      }
    }
    $valid_ids = array_unique($valid_ids);

    if (empty($valid_ids)) {
      $this->logger->warning('No valid revision IDs provided');
      return 0;
    }

    $storage = $this->getCohesionLayoutStorage();
    if (!$storage) {
      return 0;
    }

    $deleted = 0;
    $skipped = 0;
    // Capped at MAX_ERROR_LOG_DETAILS entries.
    $error_details = [];

    foreach ($valid_ids as $revision_id) {
      try {
        $revision = $storage->loadRevision($revision_id);
        if (!$revision) {
          $skipped++;
          if (count($error_details) < self::MAX_ERROR_LOG_DETAILS) {
            $error_details[] = "Revision {$revision_id}: not found (already deleted)";
          }
          $this->logger->info('Orphan cleanup: Revision @id not found, already deleted', ['@id' => $revision_id]);
          continue;
        }

        // Verify this is actually a cohesion_layout entity.
        if ($revision->getEntityTypeId() !== 'cohesion_layout') {
          $skipped++;
          if (count($error_details) < self::MAX_ERROR_LOG_DETAILS) {
            $error_details[] = "Revision {$revision_id}: wrong entity type ({$revision->getEntityTypeId()})";
          }
          $this->logger->warning('Orphan cleanup: Skipped non-cohesion_layout revision @id (type: @type)', [
            '@id' => $revision_id,
            '@type' => $revision->getEntityTypeId(),
          ]);
          continue;
        }

        // Never delete default revisions — they are the canonical entity state.
        if ($revision->isDefaultRevision()) {
          $skipped++;
          if (count($error_details) < self::MAX_ERROR_LOG_DETAILS) {
            $error_details[] = "Revision {$revision_id}: is default revision (protected)";
          }
          $this->logger->info('Orphan cleanup: Skipped default revision @id for entity @entity_id', [
            '@id' => $revision_id,
            '@entity_id' => $revision->id(),
          ]);
          continue;
        }

        $storage->deleteRevision($revision_id);
        $deleted++;
        $this->logger->info('Orphan cleanup: Successfully deleted revision @id', ['@id' => $revision_id]);
      }
      catch (\Exception $e) {
        $skipped++;
        if (count($error_details) < self::MAX_ERROR_LOG_DETAILS) {
          $error_details[] = "Revision {$revision_id}: {$e->getMessage()}";
        }
        $this->logger->error('Orphan cleanup: Failed to delete revision @id - @error', [
          '@id' => $revision_id,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    if ($skipped > 0) {
      $this->logger->warning('Orphan cleanup: Skipped @skipped of @total revisions. Details: @details', [
        '@skipped' => $skipped,
        '@total' => count($valid_ids),
        '@details' => implode('; ', $error_details),
      ]);

      if ($context === 'drush') {
        $this->messenger->addWarning(t('@skipped revisions were skipped. Check logs for details: drush watchdog:show --type=cohesion', [
          '@skipped' => $skipped,
        ]));
      }
    }

    return $deleted;
  }

  /**
   * Get cohesion layout storage.
   *
   * @return \Drupal\Core\Entity\RevisionableStorageInterface|null
   *   The cohesion layout storage or NULL.
   */
  protected function getCohesionLayoutStorage(): ?RevisionableStorageInterface {
    try {
      $storage = $this->entityTypeManager->getStorage('cohesion_layout');
      if (!$storage instanceof RevisionableStorageInterface) {
        $this->logger->error('Storage does not support revisions.');
        return NULL;
      }
      return $storage;
    }
    catch (\Exception $e) {
      $this->logger->error('Could not load storage: @error', ['@error' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Process cron cleanup.
   *
   * @return array
   *   Result array with status information.
   */
  public function processCronCleanup(): array {
    $config = $this->configFactory->get('cohesion.settings');
    if (!$config->get('delete_orphaned_revisions_on_cron')) {
      return ['status' => 'disabled'];
    }

    if (!$this->shouldRunCleanup($config)) {
      return ['status' => 'skipped'];
    }

    if (!$this->lock->acquire('cohesion_orphan_cleanup', 3600)) {
      return ['status' => 'skipped'];
    }

    try {
      // SQL COUNT — safe for any table size.
      $orphan_count = $this->countOrphanedRevisions();

      if ($orphan_count === 0) {
        // Advance the throttle interval even when nothing is found.
        $this->state->set('cohesion_orphan_cleanup_last_run', $this->time->getRequestTime());
        return ['status' => 'completed', 'orphans_found' => 0];
      }

      $this->logger->info('Cron cleanup: Found @count orphaned revisions, queuing work tokens.', [
        '@count' => $orphan_count,
      ]);

      // Queue a single self-re-queuing work token.
      $result = $this->queueWorkTokens();

      // Only advance the throttle timestamp when queuing succeeded.
      // A queue failure should not block the next cron window.
      if ($result['queued_items'] > 0) {
        $this->state->set('cohesion_orphan_cleanup_last_run', $this->time->getRequestTime());
      }

      return [
        'status' => 'completed',
        'orphans_found' => $orphan_count,
        'batches' => $result['batches'],
        'queued_items' => $result['queued_items'],
        'failed_items' => $result['failed_items'],
      ];
    }
    catch (\Throwable $e) {
      $this->logger->error('Cron cleanup error: @error', ['@error' => $e->getMessage()]);
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
    finally {
      $this->lock->release('cohesion_orphan_cleanup');
    }
  }

  /**
   * Determine whether enough time has elapsed since the last cleanup run.
   *
   * @param \Drupal\Core\Config\ImmutableConfig $config
   *
   * @return bool
   */
  protected function shouldRunCleanup(ImmutableConfig $config): bool {
    $last_run = $this->state->get('cohesion_orphan_cleanup_last_run', 0);
    if ($last_run === 0) {
      return TRUE;
    }
    $interval_hours = (int) ($config->get('orphan_cleanup_interval') ?? 24);
    $interval_hours = max(0, $interval_hours);
    return ($this->time->getRequestTime() - $last_run) >= ($interval_hours * 3600);
  }

  /**
   * Queue a single self-re-queuing work token for cron cleanup.
   *
   * One token is queued per cron run. The worker deletes a batch, then
   * re-queues itself if orphans remain — avoiding the duplicate-query
   * problem that arises when N identical tokens all start from range(0, limit).
   *
   * @return array
   *   Keys: batches (always 1), queued_items, failed_items.
   */
  protected function queueWorkTokens(): array {
    return $this->requeueWorkToken(COHESION_ORPHANS_CRON_BATCH_SIZE) ?
      ['batches' => 1, 'queued_items' => 1, 'failed_items' => 0] :
      ['batches' => 1, 'queued_items' => 0, 'failed_items' => 1];
  }

  /**
   * Enqueue a single work token.
   *
   * Public so the queue worker can re-queue via the injected manager
   * instead of calling \Drupal::queue() statically.
   *
   * @param int $batch_size
   *
   * @return bool
   *   TRUE on success, FALSE on failure.
   */
  public function requeueWorkToken(int $batch_size): bool {
    try {
      $batch_size = max(1, $batch_size);
      $this->queueFactory->get('cohesion_orphan_cleanup')
        ->createItem(['type' => 'work_token', 'batch_size' => $batch_size]);
      return TRUE;
    }
    catch (\Throwable $e) {
      $this->logger->error('Failed to queue work token: @error', ['@error' => $e->getMessage()]);
      return FALSE;
    }
  }

}
