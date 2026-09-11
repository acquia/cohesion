<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Tags list builder.
 *
 * @package Drupal\cohesion_elements
 */
class TagsListBuilder extends ConfigEntityListBuilder {

  /**
   * @return array
   */
  public function render() {
    $build = parent::render();

    // Attach required libraries.
    $build['#attached']['library'][] = 'cohesion/cohesion-admin-styles';
    $build['#attached']['library'][] = 'cohesion_elements/component-category';
    $build['table']['#attributes']['class'][] = 'ssa-tag-list';

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {

    $header['label'] = [
      'data' => $this->t('Title'),
      'width' => '40%',
    ];

    $header['type'] = [
      'data' => $this->t('Machine Name (id)'),
      'width' => '20%',
    ];

    $header['class'] = [
      'data' => $this->t('Color'),
      'width' => '10%',
    ];

    $header['in_use'] = [
      'data' => $this->t('In use'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    $header['locked'] = [
      'data' => $this->t('Locked'),
      'class' => [RESPONSIVE_PRIORITY_LOW],
    ];

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label']['data'] = $entity->label();
    $row['label']['class'][] = 'tag-label';
    $row['type']['data']['#markup'] = $entity->id();
    $row['class']['data']['#markup'] = '<div class="coh-category-color-item ' . $entity->getClass() . '"></div>';
    $row['in_use']['data']['#markup'] = $entity->getInUseMarkup();
    $row['locked']['data']['#markup'] = $entity->isLocked() ? 'Locked' : 'Unlocked';

    return $row + parent::buildRow($entity);
  }

}
