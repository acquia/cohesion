<?php

namespace Drupal\cohesion_sync;

use Drupal\cohesion_sync\Form\ListBuilderFilterForm;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Url;
use Drupal\Core\State\StateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

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
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The form builder.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

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
   * @param \Symfony\Component\HttpFoundation\Request $current_request
   *   The current request.
   * @param \Drupal\Core\Form\FormBuilderInterface $form_builder
   *   The form builder.
   *
   * @return \Drupal\Core\Entity\EntityListBuilder
   *   A new instance of the entity handler.
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new self(
      $entity_type,
      $container->get('entity_type.manager')->getStorage($entity_type->id()),
      $container->get('state'),
      $container->get('config.factory'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('form_builder'),
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
    ConfigFactoryInterface $config_factory,
    Request $current_request,
    FormBuilderInterface $form_builder,
  ) {
    parent::__construct($entity_type, $storage);
    $this->state = $state;
    $this->config_factory = $config_factory;
    $this->cohesionSettings = $config_factory->get('cohesion.settings');
    $this->currentRequest = $current_request;
    $this->formBuilder = $form_builder;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    $keys = $this->currentRequest->query->get('search');
    $build['packages_admin_filter_form'] = $this->formBuilder->getForm(ListBuilderFilterForm::class, $keys);
    $build += parent::render();

    $build['table']['#empty'] = $this->t('No sync packages available. <a href=":link">Add sync package</a>.', [':link' => Url::fromRoute('entity.cohesion_sync_package.add_form')->toString()]);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function load() {
    $entity_ids = $this->getEntityIds();
    return $this->storage->loadMultipleOverrideFree($entity_ids);
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getStorage()->getQuery()
      ->accessCheck(TRUE)
      ->sort($this->entityType->getKey('id'));

    $search = $this->currentRequest->query->get('search');
    if ($search) {
      $group = $query->orConditionGroup()
        ->condition('label', $search, 'CONTAINS')
        ->condition('description', $search, 'CONTAINS');

      $query->condition($group);
    }

    // Only add the pager if a limit is specified.
    if ($this->limit) {
      $query->pager($this->limit);
    }

    // Allow the entity query to sort using the table header.
    $header = $this->buildHeader();
    $query->tableSort($header);

    return $query->execute();
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $header = [
      'label' => [
        'data' => $this->t('Name'),
        'field' => 'label',
        'specifier' => 'label',
        'width' => '15%',
        'sort' => 'asc',
      ],
      'description' => [
        'data' => $this->t('Description'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
        'field' => 'description',
        'specifier' => 'description',
        'width' => '25%',
      ],
      'export' => [
        'data' => $this->t('Export'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        'width' => '30%',
      ],
    ];

    // Only show Legacy export option if user has turned it on.
    if ($this->cohesionSettings->get('sync_legacy_visibility')) {
      $header['legacy_export'] = [
        'data' => $this->t('Legacy export'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
        'width' => '30%',
      ];
    }

    return $header + parent::buildHeader();
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
