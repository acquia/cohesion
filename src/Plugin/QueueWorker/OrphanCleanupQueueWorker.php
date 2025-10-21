<?php

namespace Drupal\cohesion\Plugin\QueueWorker;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Drupal\cohesion\CohesionLayoutRevisionManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Processes orphaned cohesion layout revision cleanup.
 *
 * @QueueWorker(
 *   id = "cohesion_orphan_cleanup",
 *   title = @Translation("Orphaned Cohesion Layout Revision Cleanup"),
 *   cron = {"time" = 60}
 * )
 */
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
    if (!isset($data['revision_ids']) || !is_array($data['revision_ids'])) {
      $this->logger->error('Invalid queue item data');
      return;
    }

    try {
      $deleted_count = $this->layoutRevisionManager->batchDeleteRevisions($data['revision_ids'], 'queue');
      $this->logger->info('Queue processed @count orphaned revisions', ['@count' => $deleted_count]);
    }
    catch (\Exception $e) {
      $this->logger->error('Queue processing error: @error', ['@error' => $e->getMessage()]);
    }
  }

}
