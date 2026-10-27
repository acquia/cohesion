<?php

namespace Drupal\cohesion_style_guide;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 *
 */
class StyleGuideListBuilder extends CohesionListBuilder {

  /**
   * @var string
   */
  protected $formId = 'cohesion_style_guide_form';

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

    return $header;
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);
    unset($row['type']);

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildForm($form, $form_state);

    // Include the Angular css (which controls the cohesion_accordion and
    // other form styling).
    $form['#attached']['library'][] = 'cohesion/cohesion-admin-styles';

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    \Drupal::messenger()->addMessage(t('Style guide order has been updated.'), 'status');
  }

}
