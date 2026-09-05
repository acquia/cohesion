<?php

namespace Drupal\cohesion\Drush;

use Drupal\cohesion\Controller\AdministrationController;
use Drupal\cohesion_website_settings\Controller\WebsiteSettingsController;

/**
 * Helper class for import/rebuild.
 *
 * This class provides static helper methods for Site Studio Drush commands,
 * including import/rebuild operations and cohesion layout revision management.
 *
 * @package Drupal\cohesion\Drush
 */
final class DX8CommandHelpers {

  /**
   * Import s3forms and rebuild element styles.
   */
  public static function import() {
    $config = \Drupal::config('cohesion.settings');

    if ($config->get('api_key') !== '') {
      // Get a list of the batch items.
      $batch = AdministrationController::batchAction(TRUE);

      if (isset($batch['error'])) {
        return $batch;
      }

      foreach ($batch['operations'] as $operation) {
        $context = ['results' => []];
        $function = $operation[0];
        $args = $operation[1];

        if (function_exists($function)) {
          call_user_func_array($function, array_merge($args, [&$context]));
        }
      }

      // Give access to all routes.
      // Enable the routes.
      cohesion_website_settings_batch_import_finished(TRUE, $context['results'], '');

      if (isset($context['results']['error'])) {
        return ['error' => $context['results']['error']];
      }
    }
    else {
      return ['error' => t('Your Site Studio API KEY has not been set.') . $config->get('site_id')];
    }

    return FALSE;

  }

  /**
   * Resave all Site Studio config entities.
   *
   * @param array $options
   *
   * @return mixed
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function rebuild(array $options = ['no-cache-clear' => FALSE]) {
    // Reset temporary template list.
    $batch = WebsiteSettingsController::batch(TRUE, $options['verbose'], $options['no-cache-clear']);
    batch_set($batch);
    $batch['progressive'] = FALSE;
    return drush_backend_batch_process();
  }

  /**
   * Run orphan cleanup via Drupal's batch API.
   *
   * Splits the work into COHESION_ORPHANS_DRUSH_BATCH_SIZE chunks. Each batch
   * operation issues a fresh SQL LIMIT query so PHP memory usage is bounded
   * regardless of how many orphans exist.
   *
   * Must be called from a Drush command context (uses
   * drush_backend_batch_process()).
   *
   * @param bool $dry_run
   *   When TRUE, only count and report — do not delete.
   * @param int|null $orphan_count
   *   Pre-computed count from countOrphanedRevisions(). Avoids a redundant
   *   SQL COUNT when the caller already has the value.
   *
   * @return array
   *   Return value of drush_backend_batch_process(), or [] for dry-run/empty.
   */
  public static function cleanupOrphansBatch(bool $dry_run = FALSE, ?int $orphan_count = NULL): array {
    $manager = \Drupal::service('cohesion.layout_revision_manager');
    $orphan_count ??= $manager->countOrphanedRevisions();

    if ($orphan_count === 0) {
      \Drupal::messenger()->addMessage(t('No orphaned cohesion layout revisions found.'));
      return [];
    }

    if ($dry_run) {
      \Drupal::messenger()->addMessage(t('DRY-RUN: Found @count orphaned revisions that would be deleted.', [
        '@count' => $orphan_count,
      ]));
      return [];
    }

    $batch = [
      'title' => t('Cleaning up @count orphaned cohesion layout revisions', ['@count' => $orphan_count]),
      // Single sandbox operation — calls itself until no orphans remain.
      // Keeps the batch definition small regardless of orphan count.
      'operations' => [[[DX8CommandHelpers::class, 'processBatchOperation'], [COHESION_ORPHANS_DRUSH_BATCH_SIZE]]],
      'finished' => [DX8CommandHelpers::class, 'processBatchFinished'],
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
