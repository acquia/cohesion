<?php

namespace Drupal\cohesion_elements;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CategoriesListBuilder.
 *
 * @package Drupal\cohesion_elements
 */
class CategoriesListBuilder extends CohesionListBuilder {

  /**
   * @var string
   */
  protected $formId = 'cohesion_categories_form';

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header = parent::buildHeader();
    unset($header['type']);

    $header['label'] = [
      'data' => $this->t('Title'),
      'width' => '40%',
    ];

    $header['class'] = [
      'data' => $this->t('Color'),
    ];

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    unset($row['type']);
    $row['label'] = '<span>' . $row['label'] . '</span>';

    $row['class']['#markup'] = '<div class="coh-category-color-item ' . $row['class']['#markup'] . '"></div>';

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Include the Angualar css (which controls the cohesion_accordion and other form styling).
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    \Drupal::messenger()->addMessage(t('Category order has been updated.'), 'status');
  }

}
