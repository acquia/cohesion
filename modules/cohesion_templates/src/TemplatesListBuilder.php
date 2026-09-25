<?php

namespace Drupal\cohesion_templates;

use Drupal\cohesion\CohesionListBuilder;
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
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

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
