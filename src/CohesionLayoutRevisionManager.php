<?php

namespace Drupal\cohesion;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Psr\Log\LoggerInterface;

/**
 * Service for managing cohesion layout revisions cleanup.
 */
class CohesionLayoutRevisionManager {

  /**
   * Cache for reference tables.
   *
   * @var array
   */
  protected $referenceTablesCache = [];

  /**
   * Constructs a CohesionLayoutRevisionManager object.
   */
  public function __construct(
    protected Connection $database,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected LoggerInterface $logger,
    protected MessengerInterface $messenger,
  ) {
  }

  /**
   * Find orphaned cohesion layout revisions from ALL entity types.
   *
   * @return array
   *   Array of orphaned revision IDs.
   */
  public function findOrphanedRevisions() {
    try {
      $all_revisions = $this->getAllRevisions();
      if (empty($all_revisions)) {
        return [];
      }

      $referenced_revisions = $this->getReferencedRevisions();
      $orphaned = array_diff($all_revisions, $referenced_revisions);

      // CRITICAL: Verify these are truly orphaned by checking they don't
      // exist in the main cohesion_layout table as current revisions.
      return $this->filterOutActiveRevisions($orphaned);
    }
    catch (\Exception $e) {
      $this->logger->error('Error finding orphaned revisions: @error', ['@error' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Filter out revisions that are currently active in cohesion_layout table.
   *
   * @param array $revision_ids
   *   Array of potentially orphaned revision IDs.
   *
   * @return array
   *   Array of confirmed orphaned revision IDs.
   */
  protected function filterOutActiveRevisions(array $revision_ids) {
    if (empty($revision_ids)) {
      return [];
    }

    try {
      // Get all revision IDs that are currently used in cohesion_layout table.
      $active_revisions = $this->database->select('cohesion_layout', 'cl')
        ->fields('cl', ['revision'])
        ->condition('cl.revision', $revision_ids, 'IN')
        ->execute()
        ->fetchCol();

      // Return only revisions NOT in the active list.
      return array_diff($revision_ids, $active_revisions);
    }
    catch (\Exception $e) {
      $this->logger->error('Error filtering active revisions: @error', ['@error' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Get all cohesion layout field revisions.
   *
   * @return array
   *   Array of all revision IDs.
   */
  protected function getAllRevisions() {
    try {
      if (!$this->database->schema()->tableExists('cohesion_layout_field_revision')) {
        $this->logger->warning('Table cohesion_layout_field_revision does not exist');
        return [];
      }

      $revisions = $this->database->select('cohesion_layout_field_revision', 'clfr')
        ->fields('clfr', ['revision'])
        ->execute()
        ->fetchCol();

      // Ensure all values are integers.
      return array_map('intval', array_filter($revisions, 'is_numeric'));
    }
    catch (\Exception $e) {
      $this->logger->error('Error getting all revisions: @error', ['@error' => $e->getMessage()]);
      return [];
    }
  }

  /**
   * Get all referenced cohesion layout revisions.
   *
   * @return array
   *   Array of referenced revision IDs.
   */
  protected function getReferencedRevisions() {
    $referenced_revisions = [];
    $reference_tables = $this->getCohesionReferenceTables();

    foreach ($reference_tables as $table_info) {
      try {
        $revisions = $this->database->select($table_info['table'], 't')
          ->fields('t', [$table_info['field']])
          ->isNotNull($table_info['field'])
          ->condition($table_info['field'], 0, '>')
          ->execute()
          ->fetchCol();

        $referenced_revisions = array_merge($referenced_revisions, $revisions);
      }
      catch (\Exception $e) {
        $this->logger->error('Error querying @table: @error', [
          '@table' => $table_info['table'],
          '@error' => $e->getMessage(),
        ]);
      }
    }

    return array_unique($referenced_revisions);
  }

  /**
   * Get cohesion reference tables for ALL entity types.
   *
   * @return array
   *   Array of reference table information.
   */
  protected function getCohesionReferenceTables() {
    if (!empty($this->referenceTablesCache)) {
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
    catch (\Exception $e) {
      $this->logger->error('Error getting reference tables: @error', ['@error' => $e->getMessage()]);
    }

    $this->referenceTablesCache = $reference_tables;
    return $reference_tables;
  }

  /**
   * Clean up orphaned cohesion layout revisions.
   *
   * @param bool $dry_run
   *   Whether to run in dry-run mode.
   * @param string $context
   *   Context for logging.
   *
   * @return array
   *   Statistics about the cleanup operation.
   */
  public function cleanupOrphans($dry_run = FALSE, $context = '') {
    $orphaned_revisions = $this->findOrphanedRevisions();
    $orphan_count = count($orphaned_revisions);

    if ($orphan_count === 0) {
      if ($context === 'drush') {
        $this->messenger->addMessage(t('No orphaned cohesion layout revisions found.'));
      }
      return ['found' => 0, 'deleted' => 0, 'errors' => 0];
    }

    if ($dry_run) {
      if ($context === 'drush') {
        $this->messenger->addMessage(t('Found @count orphaned cohesion layout revisions.', ['@count' => $orphan_count]));
      }
      return ['found' => $orphan_count, 'deleted' => 0, 'errors' => 0];
    }

    $deleted = $this->batchDeleteRevisions($orphaned_revisions, $context);
    $errors = $orphan_count - $deleted;

    if ($context === 'drush') {
      $this->messenger->addMessage(t('Deleted @deleted orphaned cohesion layout revisions.', ['@deleted' => $deleted]));
      if ($errors > 0) {
        $this->messenger->addWarning(t('@errors errors occurred.', ['@errors' => $errors]));
      }
    }

    return ['found' => $orphan_count, 'deleted' => $deleted, 'errors' => $errors];
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
  public function batchDeleteRevisions(array $revision_ids, $context = '') {
    if (empty($revision_ids)) {
      return 0;
    }

    // Validate all revision IDs are positive integers.
    $valid_ids = array_filter($revision_ids, function ($id) {
      return is_numeric($id) && $id > 0;
    });

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
    $error_details = [];

    foreach ($valid_ids as $revision_id) {
      try {
        $revision = $storage->loadRevision($revision_id);
        if (!$revision) {
          $skipped++;
          $error_details[] = "Revision {$revision_id}: not found (already deleted)";
          $this->logger->info('Orphan cleanup: Revision @id not found, already deleted', ['@id' => $revision_id]);
          continue;
        }

        // EDGE CASE: Verify this is actually a cohesion_layout entity.
        if ($revision->getEntityTypeId() !== 'cohesion_layout') {
          $skipped++;
          $error_details[] = "Revision {$revision_id}: wrong entity type ({$revision->getEntityTypeId()})";
          $this->logger->warning('Orphan cleanup: Skipped non-cohesion_layout revision @id (type: @type)', [
            '@id' => $revision_id,
            '@type' => $revision->getEntityTypeId(),
          ]);
          continue;
        }

        // EDGE CASE: Never delete default revisions.
        if ($revision->isDefaultRevision()) {
          $skipped++;
          $error_details[] = "Revision {$revision_id}: is default revision (protected)";
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
        $error_details[] = "Revision {$revision_id}: {$e->getMessage()}";
        $this->logger->error('Orphan cleanup: Failed to delete revision @id - @error', [
          '@id' => $revision_id,
          '@error' => $e->getMessage(),
        ]);
      }
    }

    if ($skipped > 0) {
      // Log detailed error summary to database.
      $this->logger->warning('Orphan cleanup: Skipped @skipped of @total revisions. Details: @details', [
        '@skipped' => $skipped,
        '@total' => count($revision_ids),
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
  protected function getCohesionLayoutStorage() {
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
  public function processCronCleanup() {
    $config = \Drupal::config('cohesion.settings');
    if (!$config->get('delete_orphaned_revisions_on_cron', FALSE)) {
      return ['status' => 'disabled'];
    }

    if (!cohesion_should_run_cleanup()) {
      return ['status' => 'skipped'];
    }

    $lock = \Drupal::lock();
    if (!$lock->acquire('cohesion_orphan_cleanup', 3600)) {
      return ['status' => 'skipped'];
    }

    try {
      \Drupal::state()->set('cohesion_orphan_cleanup_last_run', \Drupal::time()->getRequestTime());

      $orphaned_revisions = $this->findOrphanedRevisions();
      $orphan_count = count($orphaned_revisions);

      if ($orphan_count === 0) {
        return ['status' => 'completed', 'orphans_found' => 0];
      }

      if ($orphan_count > COHESION_MAX_CRON_ORPHANS) {
        $this->logger->error('Too many orphans found (@count)', ['@count' => $orphan_count]);
        return ['status' => 'aborted', 'orphans_found' => $orphan_count];
      }

      $result = $this->queueOrphanedRevisions($orphaned_revisions);

      return [
        'status' => 'completed',
        'orphans_found' => $orphan_count,
        'batches' => $result['batches'],
        'queued_items' => $result['queued_items'],
        'failed_items' => $result['failed_items'],
      ];
    }
    catch (\Exception $e) {
      $this->logger->error('Cron cleanup error: @error', ['@error' => $e->getMessage()]);
      return ['status' => 'error', 'message' => $e->getMessage()];
    }
    finally {
      $lock->release('cohesion_orphan_cleanup');
    }
  }

  /**
   * Queue orphaned revisions for processing.
   *
   * @param array $orphaned_revisions
   *   Array of orphaned revision IDs.
   *
   * @return array
   *   Result array with queue statistics.
   */
  protected function queueOrphanedRevisions(array $orphaned_revisions) {
    $queue = \Drupal::queue('cohesion_orphan_cleanup');
    $batch_size = COHESION_CRON_BATCH_SIZE;
    $chunks = array_chunk($orphaned_revisions, $batch_size);
    $queued_items = 0;
    $failed_items = 0;

    foreach ($chunks as $chunk) {
      try {
        $queue->createItem(['revision_ids' => $chunk]);
        $queued_items++;
      }
      catch (\Exception $e) {
        $failed_items++;
        $this->logger->error('Failed to queue batch: @error', ['@error' => $e->getMessage()]);
      }
    }

    return [
      'batches' => count($chunks),
      'queued_items' => $queued_items,
      'failed_items' => $failed_items,
    ];
  }

}
