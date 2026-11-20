<?php

namespace Drupal\cohesion_templates;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class TemplatesListBuilder.
 *
 * Provides a listing of Site Studio view templates.
 *
 * @package Drupal\cohesion_templates
 */
class TemplatesListBuilder extends CohesionListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    unset($header['type']);

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    unset($row['type']);

    if ($this->entityTypeId === 'cohesion_master_templates' && $entity->get('default') === TRUE) {
      $row['status']['data']['#markup'] .= ', ' . $this->t('default');
    }

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
      $operations['create'] = $operations['edit'];
      $operations['create']['title'] = t('Create');
      unset($operations['edit']);
      unset($operations['delete']);
      unset($operations['duplicate']);
      unset($operations['disable']);
      unset($operations['enable']);
    }

    return $operations;
  }

}
