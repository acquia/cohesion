<?php

namespace Drupal\cohesion_sync;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Url;

/**
 * Class PackageListBuilder.
 *
 * Defines a class to build a list of sync package entities.
 *
 * @package Drupal\cohesion_sync
 */
class PackageListBuilder extends ConfigEntityListBuilder {

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
    $header += parent::buildHeader();
    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $sync_package) {
    $row['label'] = $sync_package->label();
    $row['description'] = $sync_package->get('description');

    // Create the export to package download link.
    $url = Url::fromRoute('cohesion_sync.operation_export_single', [
      'entity_type' => $sync_package->getEntityTypeId(),
      'entity_uuid' => $sync_package->uuid(),
    ]);

    $url->setOption('query', [
      'destination' => \Drupal::request()->getRequestUri(),
    ]);

    $row['export']['data'] = [
      '#type' => 'operations',
      '#links' => [
        [
          'title' => $this->t('Export package as file'),
          'url' => $url,
        ],
      ],
    ];

    $row += parent::buildRow($sync_package);
    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $sync_package) {
    $operations = parent::getDefaultOperations($sync_package);
    /*
    $operations['duplicate'] = [
    'title' => t('Duplicate'),
    'weight' => 15,
    'url' => $sync_package->toUrl('duplicate-form'),
    ];
     */
    return $operations;
  }

}
