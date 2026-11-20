<?php

namespace Drupal\cohesion_base_styles;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class BaseStylesListBuilder.
 *
 * Provides a listing of Site Studio base styles entities.
 *
 * @package Drupal\cohesion_base_styles
 */
class BaseStylesListBuilder extends CohesionListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    unset($header['label']);

    $header['status'] = [
      'data' => $this->t('Status'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    $header['type'] = [
      'data' => $this->t('Type'),
    ];

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    $row['type'] = $entity->label();
    if (!$entity->isModified()) {
      $row['status'] = $this->t('Disabled');
    }
    unset($row['label']);
    return $row;
  }

  /**
   * {@inheritdoc}
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param \Drupal\Core\Cache\CacheableMetadata $cacheability
   */
  public function getDefaultOperations(EntityInterface $entity, ?CacheableMetadata $cacheability = NULL) {
    // Drupal 10's ConfigEntityListBuilder::getDefaultOperations() only
    // accepts the $entity argument; Drupal 11/12 added the $cacheability
    // parameter. Making it optional here keeps this override's signature
    // compatible with both.
    if ($cacheability === NULL) {
      $cacheability = new CacheableMetadata();
    }

    $operations = parent::getDefaultOperations($entity, $cacheability);

    if (!$entity->isModified()) {
      $operations['edit']['title'] = t('Create');
      unset($operations['duplicate']);
      unset($operations['disable']);
      unset($operations['enable']);
    }

    return $operations;
  }

}
