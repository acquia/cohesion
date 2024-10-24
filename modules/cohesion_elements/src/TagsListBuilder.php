<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Tags list builder.
 *
 * @package Drupal\cohesion_elements
 */
class TagsListBuilder extends CohesionListBuilder {

  /**
   * @var string
   */
  protected $formId = 'cohesion_tags_form';

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

    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    $row['type']['data'] = ['#markup' => $entity->id()];
    $row['class']['#markup'] = '<div class="coh-category-color-item ' . $row['class']['#markup'] . '"></div>';

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';
    $form['#attached']['library'][] = 'cohesion_elements/component-category';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    \Drupal::messenger()->addMessage(t('Tag order has been updated.'), 'status');
  }

}
