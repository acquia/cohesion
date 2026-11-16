<?php

namespace Drupal\cohesion_sync;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Config\Entity\ConfigEntityTypeInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Sync plugin base.
 *
 * @package Drupal\cohesion_sync
 */
abstract class SyncPluginBase extends PluginBase implements SyncPluginInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeInterface
   */
  protected $entityTypeDefinition;

  /**
   * Holds the id key of the entity type.
   *
   * @var string
   */
  protected $id_key;

  /**
   * Holds the storage of the active entity entity type.
   *
   * @var mixed
   */
  protected $entityTypeStorage;

  /**
   * SyncPluginBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityRepository $entity_repository, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Save the injected services.
    $this->entityRepository = $entity_repository;
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity.repository'),
      $container->get('entity_type.manager'),
      $container->get('string_translation')
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
  public function getInterface() {
    return $this->pluginDefinition['interface'];
  }

  /**
   * Setup.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type_definition
   *
   * @return $this
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function setType(EntityTypeInterface $entity_type_definition) {
    $this->entityTypeDefinition = $entity_type_definition;

    $this->id_key = $this->entityTypeDefinition->getKey('id');
    $this->entityTypeStorage = $this->entityTypeManager->getStorage($this->entityTypeDefinition->id());

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function validatePackageEntryShouldApply($entry) {
    // Make sure the ID key is in the entry.
    if (!isset($entry[$this->id_key])) {
      throw new \Exception(sprintf('Missing ID key "%s" for this %s import.',
        $this->id_key,
        $this->entityTypeDefinition->getLabel()
      ));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function applyPackageEntry($entry) {
    // Make sure API send() function just returns without sending anything to
    // the API.
    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');
    $cohesion_sync_lock = TRUE;
  }

  /**
   * (@inerhitDoc}
   */
  public function getActionData($entry, $action_state, $type) {
    return [
      'entry_uuid' => $entry['uuid'],
      'entry_action_state' => $action_state,
      'entity_type' => $type,
      'id' => $entry[$this->entityTypeDefinition->getKey('id')],
      'is_config' => $this->entityTypeDefinition instanceof ConfigEntityTypeInterface,
    ];
  }

}
