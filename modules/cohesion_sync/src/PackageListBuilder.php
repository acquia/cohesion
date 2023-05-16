<?php

namespace Drupal\cohesion_sync;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Url;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class PackageListBuilder.
 *
 * Defines a class to build a list of sync package entities.
 *
 * @package Drupal\cohesion_sync
 */
class PackageListBuilder extends ConfigEntityListBuilder {

  /**
   * The state store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $cohesionSettings;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $config_factory;

  /**
   * Instantiates a new instance of this entity handler.
   *
   * This is a factory method that returns a new instance of this object. The
   * factory should pass any needed dependencies into the constructor of this
   * object, but not the container itself. Every call to this method must return
   * a new instance of this object; that is, it may not implement a singleton.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container this object should use.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   *
   * @return \Drupal\Core\Entity\EntityListBuilder
   *   A new instance of the entity handler.
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('state'),
      $container->get('config.factory')
    );
  }

  /**
   * Constructs a new EntityListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The entity storage class.
   * @param \Drupal\Core\State\StateInterface $state
   *   State API service.
   */
  public function __construct(
    EntityTypeInterface $entity_type,
    EntityStorageInterface $storage,
    StateInterface $state,
    ConfigFactoryInterface $config_factory
  ) {
    parent::__construct($entity_type, $storage);
    $this->state = $state;
    $this->config_factory = $config_factory;
    $this->cohesionSettings = $config_factory->get('cohesion.settings');
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = $this->t('Name');
    $header['description'] = [
      'data' => $this->t('Description'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];
    $header['export'] = [
      'data' => $this->t('Export'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      'width' => '35%',
    ];

    // Only show Legacy export option if user has turned it on.
    if ($this->cohesionSettings->get('sync_legacy_visibility')) {
      $header['legacy_export'] = [
        'data' => $this->t('Legacy export'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        'width' => '35%',
      ];
    }

    $header += parent::buildHeader();
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $sync_package) {
    $row['label'] = $sync_package->label();
    $row['description'] = $sync_package->get('description');

    // Create the legacy export package download link.
    $destination = Url::fromRoute('<current>')->toString();
    $url = Url::fromRoute('cohesion_sync.operation_export_single', [
      'entity_type' => $sync_package->getEntityTypeId(),
      'entity_uuid' => $sync_package->uuid(),
    ],
    [
      'query' => [
        'destination' => $destination,
      ],
    ]);

    $row['export']['data'] = [
      '#type' => 'operations',
      '#links' => [
        [
          'title' => $this->t('Export as .tar.gz'),
          'url' => Url::fromRoute('cohesion_sync.export.generate_package', [
            'package' => $sync_package->id(),
          ]),
        ],
      ],
    ];

    // Only show Legacy export option if user has turned it on.
    if ($this->cohesionSettings->get('sync_legacy_visibility')) {
      $row['legacy_export']['data'] = [
        '#type' => 'operations',
        '#links' => [
          [
            'title' => $this->t('Export as YML file'),
            'url' => $url,
          ],
        ],
        '#attributes' => [
          'class' => ['legacy-export'],
        ],
      ];
    }

    $row += parent::buildRow($sync_package);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $sync_package) {
    $operations = parent::getDefaultOperations($sync_package);

    return $operations;
  }

}
