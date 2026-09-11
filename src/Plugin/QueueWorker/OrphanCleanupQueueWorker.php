<?php

namespace Drupal\cohesion\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Queue\Attribute\QueueWorker;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\cohesion\CohesionLayoutRevisionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes orphaned cohesion layout revision cleanup.
 *
 */
#[QueueWorker(
  id: 'cohesion_orphan_cleanup',
  title: new TranslatableMarkup('Orphaned Cohesion Layout Revision Cleanup'),
  cron: ['time' => 60]
)]
class OrphanCleanupQueueWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  /**
   * Constructs a new OrphanCleanupQueueWorker.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    protected CohesionLayoutRevisionManager $layoutRevisionManager,
    protected LoggerInterface $logger,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('cohesion.layout_revision_manager'),
      $container->get('logger.channel.system')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function processItem($data) {
    // Guard against malformed (non-array) payloads before reading offsets.
    if (!is_array($data)) {
      $this->logger->error('Queue item payload is not an array.');
      return;
    }
    try {
      // Work token format: do a fresh SQL LIMIT query to find current orphans.
      // This avoids holding all revision IDs in memory or in the queue table.
      if (isset($data['type']) && $data['type'] === 'work_token') {
        // Clamp batch_size to a positive integer so a malformed token
        // with batch_size=0 cannot silently disable cleanup.
        $batch_size = max(1, (int) ($data['batch_size'] ?? COHESION_ORPHANS_CRON_BATCH_SIZE));
        $orphan_ids = $this->layoutRevisionManager->findOrphanedRevisionsBatch($batch_size);
        if (!empty($orphan_ids)) {
          $deleted = $this->layoutRevisionManager->batchDeleteRevisions($orphan_ids, 'queue');
          $this->logger->info('Queue worker deleted @count orphaned revisions.', ['@count' => $deleted]);
          // Only re-queue when progress was made to prevent an infinite loop
          // when storage is unavailable or all revisions are skipped.
          if ($deleted > 0) {
            $this->layoutRevisionManager->requeueWorkToken($batch_size);
          }
          else {
            $this->logger->error('Queue worker made no progress; not re-queuing work token to avoid infinite loop.');
          }
        }
        return;
      }

      // Legacy format: revision IDs passed directly in the queue item.
      if (isset($data['revision_ids']) && is_array($data['revision_ids'])) {
        $deleted_count = $this->layoutRevisionManager->batchDeleteRevisions($data['revision_ids'], 'queue');
        $this->logger->info('Queue worker deleted @count orphaned revisions.', ['@count' => $deleted_count]);
        return;
      }

      $this->logger->error('Invalid queue item data: missing type or revision_ids key.');
    }
    catch (\Throwable $e) {
      $this->logger->error('Queue processing error: @error', ['@error' => $e->getMessage()]);
      // Rethrow so the queue runner marks this item as failed and can retry
      // it later, rather than silently consuming it as successfully processed.
      throw $e;
    }
  }

}
