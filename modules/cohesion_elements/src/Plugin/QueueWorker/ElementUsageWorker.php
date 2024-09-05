<?php

namespace Drupal\cohesion_elements\Plugin\QueueWorker;

use Drupal\cohesion\ExceptionLoggerTrait;
use Drupal\Core\Database\Connection;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Queue\QueueWorkerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @QueueWorker(
 *   id = "element_usage",
 *   title = @Translation("Element usage worker"),
 *   cron = {"time" = 30}
 * )
 */
class ElementUsageWorker extends QueueWorkerBase implements ContainerFactoryPluginInterface {

  use ExceptionLoggerTrait;

  /**
   * Holds the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * {@inheritDoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->connection = $connection;
  }

  /**
   * {@inheritDoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('database'),
    );
  }

  /**
   * {@inheritDoc}
   */
  public function processItem($data) {
    foreach ($data['entities'] as $entity) {
      $canvasData = $entity->getDecodedJsonValues()['canvas'];

      foreach ($canvasData as $item) {
        // Check the item on the canvas is a default "element".
        if (array_key_exists($item['uid'], $data['elements'])) {
          $sourceUuid = $entity->uuid();
          $sourceType = $entity->getEntityTypeId();
          // If it's a cohesion layout on a node we need to get the parent info.
          if ($entity->getEntityTypeId() === 'cohesion_layout') {
            $parentEntity = $entity->getParentEntity();
            $sourceUuid = $parentEntity->uuid();
            $sourceType = $parentEntity->getEntityTypeId();
          }

          // Insert element usage into the database.
          try {
            $this->connection->insert('coh_element_usage')->fields([
              'element' => $item['uid'],
              'source_uuid' => $sourceUuid,
              'source_type' => $sourceType,
            ])->execute();
          } catch (\Exception $e) {
            // DB connection problem.
            $this->logException($e);
            return [];
          }
        }
      }
    }

    // Update element usage last run date/time.
    $dateTime = \Drupal::time()->getCurrentTime();
    \Drupal::service('config.factory')->getEditable('cohesion.settings')->set('element_usage_last_run', $dateTime)->save();
  }

}
