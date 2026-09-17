<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for grouped of entities on the UI.
 *
 * @package Drupal\cohesion
 */
abstract class EntityGroupsPluginBase extends PluginBase implements EntityGroupsPluginInterface, ContainerFactoryPluginInterface {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * For calculating where entities are in use.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * Holds the storage manager for the entity.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * EntityGroupsPluginBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager, UsageUpdateManager $usage_update_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Save the injected services.
    $this->storage = $entity_type_manager->getStorage($this->getEntityType());
    $this->entityTypeManager = $entity_type_manager;
    $this->usageUpdateManager = $usage_update_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('cohesion_usage.update_manager')
    );
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

}
