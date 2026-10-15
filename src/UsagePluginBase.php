<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for usage plugin.
 *
 * @package Drupal\cohesion
 */
abstract class UsagePluginBase extends PluginBase implements UsagePluginInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The StreamWrapperManager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * The entity storage for the entity type this plugin works for.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * UsagePluginBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   * @param \Drupal\Core\Database\Connection $connection
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, StreamWrapperManager $stream_wrapper_manager, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Save the injected services.
    $this->entityTypeManager = $entity_type_manager;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->connection = $connection;

    try {
      $this->storage = $this->entityTypeManager->getStorage($this->getEntityType());
    }
    // Entity defined in the Usage plugin was not found.
    catch (PluginNotFoundException $e) {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('entity_type.manager'), $container->get('stream_wrapper_manager'), $container->get('database'));
  }

  /**
   * Getter.
   */
  public function getStorage() {
    return $this->storage;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityType() {
    return $this->pluginDefinition['entity_type'];
  }

  /**
   *
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = [];

    foreach ($data as $entry) {
      if ($entry['type'] == 'entity_uuid') {
        $entities[] = [
          'type' => $entry['entity_type'],
          'uuid' => $entry['uuid'],
        ];
      }
    }

    return $entities;
  }

}
