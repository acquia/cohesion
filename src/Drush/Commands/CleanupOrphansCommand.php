<?php

namespace Drupal\cohesion\Drush\Commands;

use Drupal\cohesion\CohesionLayoutRevisionManager;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to clean up orphaned cohesion layout revisions.
 */
final class CleanupOrphansCommand extends DrushCommands {

  use StringTranslationTrait;

  const NAME = 'sitestudio:cleanup-orphans';

  /**
   * CleanupOrphansCommand constructor.
   */
  public function __construct(
    protected CohesionLayoutRevisionManager $layoutRevisionManager,
    TranslationInterface $stringTranslation,
  ) {
    parent::__construct();
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('cohesion.layout_revision_manager'),
      $container->get('string_translation'),
    );
  }

  /**
   * Clean up orphaned cohesion layout revisions.
   *
   * @command sitestudio:cleanup-orphans
   * @aliases cohesion:cleanup-orphans
   * @option dry-run Run in dry-run mode without deleting any data.
   * @usage sitestudio:cleanup-orphans --dry-run Report orphaned revisions without deleting them.
   * @usage sitestudio:cleanup-orphans Delete orphaned revisions.
   */
  #[CLI\Command(name: self::NAME, aliases: ['cohesion:cleanup-orphans'])]
  #[CLI\Option(name: 'dry-run', description: 'Run in dry-run mode without deleting any data.')]
  #[CLI\Usage(name: 'sitestudio:cleanup-orphans --dry-run', description: 'Report orphaned revisions without deleting them.')]
  #[CLI\Usage(name: 'sitestudio:cleanup-orphans', description: 'Delete orphaned revisions.')]
  public function cleanupOrphans(array $options = ['dry-run' => FALSE]): int {
    $io = $this->io();
    $dry_run = (bool) $options['dry-run'];

    if ($dry_run) {
      $io->writeln('Running in DRY-RUN mode - no data will be deleted.');
    }

    // Use a SQL COUNT query — safe for any table size, no PHP memory impact.
    $orphan_count = $this->layoutRevisionManager->countOrphanedRevisions();

    if ($orphan_count === 0) {
      $io->writeln('No orphaned cohesion layout revisions found.');
      return self::EXIT_SUCCESS;
    }

    $io->writeln("Found {$orphan_count} orphaned cohesion layout revisions.");

    if ($dry_run) {
      $io->writeln("DRY-RUN: Would delete {$orphan_count} orphaned revisions.");
      return self::EXIT_SUCCESS;
    }

    $confirmed = $this->input()->hasOption('yes') && $this->input()->getOption('yes');
    if (!$confirmed && !$io->confirm('Are you sure you want to delete these orphaned revisions?', FALSE)) {
      $io->writeln('Operation cancelled.');
      return self::EXIT_SUCCESS;
    }

    $io->writeln('Processing in batches of ' . COHESION_ORPHANS_DRUSH_BATCH_SIZE . ' via Drupal batch API...');
    $result = $this->runBatch($orphan_count);

    return is_array($result) && isset(array_shift($result)['error']) ? self::EXIT_FAILURE : self::EXIT_SUCCESS;
  }

  /**
   * Runs the orphan cleanup via Drupal's batch API.
   *
   * Splits the work into COHESION_ORPHANS_DRUSH_BATCH_SIZE chunks. Each batch
   * operation issues a fresh SQL LIMIT query so PHP memory usage is bounded
   * regardless of how many orphans exist.
   *
   * @param int $orphan_count
   *   Pre-computed count from countOrphanedRevisions().
   *
   * @return mixed
   *   Return value of drush_backend_batch_process().
   */
  protected function runBatch(int $orphan_count): mixed {
    $batch = [
      'title' => $this->t('Cleaning up @count orphaned cohesion layout revisions', ['@count' => $orphan_count]),
      // Single sandbox operation — calls itself until no orphans remain.
      // Keeps the batch definition small regardless of orphan count.
      'operations' => [[[self::class, 'processBatchOperation'], [COHESION_ORPHANS_DRUSH_BATCH_SIZE]]],
      'finished' => [self::class, 'processBatchFinished'],
      'progressive' => FALSE,
    ];

    batch_set($batch);
    return drush_backend_batch_process();
  }

  /**
   * Drupal batch API operation callback.
   *
   * Issues a fresh SQL LIMIT query for orphans and deletes them. Because
   * previously processed orphans are deleted before this runs, no offset is
   * needed — the query always returns the next available batch.
   *
   * @param int $batch_size
   *   Number of revisions to find and delete in this operation.
   * @param array $context
   *   Batch API context array (passed by reference).
   */
  public static function processBatchOperation(int $batch_size, array &$context): void {
    $manager = \Drupal::service('cohesion.layout_revision_manager');

    $orphan_ids = $manager->findOrphanedRevisionsBatch($batch_size);
    if (empty($orphan_ids)) {
      // No more orphans — signal batch completion.
      $context['finished'] = 1;
      return;
    }

    $deleted = $manager->batchDeleteRevisions($orphan_ids, 'drush');
    $context['results']['deleted'] = ($context['results']['deleted'] ?? 0) + $deleted;
    $context['message'] = t('Deleted @count orphaned revisions...', [
      '@count' => $context['results']['deleted'],
    ]);

    // Guard against infinite loops: if orphans were found but nothing was
    // deleted (storage unavailable, all skipped), stop the batch.
    if ($deleted === 0) {
      $context['results']['stalled'] = TRUE;
      $context['finished'] = 1;
      return;
    }

    // finished < 1 tells the batch runner to invoke this operation again.
    $context['finished'] = 0;
  }

  /**
   * Drupal batch API finished callback.
   *
   * @param bool $success
   *   TRUE if the batch completed without a fatal error.
   * @param array $results
   *   Accumulated results from all batch operations.
   */
  public static function processBatchFinished(bool $success, array $results): void {
    $deleted = $results['deleted'] ?? 0;
    if (!empty($results['stalled'])) {
      \Drupal::messenger()->addError(t('Cleanup stalled after deleting @count revisions. Check logs for details.', [
        '@count' => $deleted,
      ]));
      return;
    }
    if ($success) {
      \Drupal::messenger()->addMessage(t('Cleanup complete. Deleted @count orphaned cohesion layout revisions.', [
        '@count' => $deleted,
      ]));
    }
    else {
      \Drupal::messenger()->addError(t('Cleanup encountered errors. Deleted @count revisions before stopping.', [
        '@count' => $deleted,
      ]));
    }
  }

}
