<?php

namespace Drupal\cohesion;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Site Studio custom styles entities.
 */
class CohesionListBuilder extends DraggableListBuilder {

  /**
   * The unique form ID (override this when subclassing).
   *
   * @var string
   */
  protected $formId = 'cohesion_form';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->formId;
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = [];
    $header['label'] = $this->t('Title');

    if ($this->entityType->hasKey('class')) {
      $header['class'] = [
        'data' => $this->t('Class'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ];
    }

    $header['type'] = [
      'data' => $this->t('Type'),
      'class' => [RESPONSIVE_PRIORITY_MEDIUM],
    ];

    if ($this->entityType->hasKey('status')) {
      $header['status'] = $this->t('Status');
    }

    if ($this->entityType->hasKey('selectable')) {
      $header['selectable'] = [
        'data' => $this->t('Selection'),
        'class' => [RESPONSIVE_PRIORITY_MEDIUM],
      ];
    }

    if ($this->entityType->hasLinkTemplate('in-use')) {
      $header['in_use'] = [
        'data' => $this->t('In use'),
        'class' => [RESPONSIVE_PRIORITY_LOW],
      ];
    }

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
    /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity */
    $row = [];

    $row['label'] = $entity->label();
    $row['type'] = [];

    if ($this->entityType->hasKey('class')) {
      $row['class'] = ['#markup' => $entity->getClass()];
    }

    if ($entity->getEntityType()->hasKey('status')) {
      $row['status']['data'] = ['#markup' => $entity->status() ? $this->t('Enabled') : $this->t('Disabled')];
    }

    if ($entity->getEntityType()->hasKey('selectable')) {
      $row['selectable']['data'] = ['#markup' => $entity->isSelectable() ? $this->t('Enabled') : $this->t('Disabled')];
    }

    if ($this->entityType->hasLinkTemplate('in-use')) {
      $row['in_use']['data'] = ['#markup' => $entity->getInUseMarkup()];
    }

    $row['locked']['data'] = [
      '#markup' => $entity->isLocked() ? 'Locked' : 'Unlocked',
    ];

    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    if ($entity->hasLinkTemplate('duplicate-form')) {
      $operations['duplicate'] = [
        'title' => $this->t('Duplicate'),
        'url' => $entity->toUrl('duplicate-form'),
        'weight' => 44,
      ];
    }

    if ($entity->hasLinkTemplate('set-default-form')) {
      $operations['set_default'] = [
        'title' => $this->t('Set as default'),
        'url' => $entity->toUrl('set-default-form'),
        'weight' => 45,
      ];
    }

    if ($this->entityType->hasKey('selectable') && $entity->get('status', TRUE)) {
      if (!$entity->isSelectable() && $entity->hasLinkTemplate('enable-selection')) {
        $operations['enable_selection'] = [
          'title' => $this->t('Enable selection'),
          'url' => $entity->toUrl('enable-selection'),
          'weight' => 46,
        ];
      }
      elseif ($entity->hasLinkTemplate('disable-selection')) {
        $operations['disable_selection'] = [
          'title' => $this->t('Disable selection'),
          'url' => $entity->toUrl('disable-selection'),
          'weight' => 47,
        ];
      }
    }

    return $operations;
  }

}
