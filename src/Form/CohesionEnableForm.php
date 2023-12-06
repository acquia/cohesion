<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionEnableForm.
 *
 * Builds the form to enable a cohesion entity.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionEnableForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to enable the @entity_type %entity_name?', [
      '@entity_type' => strtolower($this->entity->getEntityType()->get('label_singular')),
      '%entity_name' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->entity->toUrl('collection');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Enable');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Enabling a %entity_type_label will make it available to use once again.', [
      '%entity_type_label' => $this->entity->getEntityType()->get('label_singular'),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->set('status', TRUE);
    $this->entity->save();

    \Drupal::messenger()->addMessage($this->t('%entity_name successfully enabled.', [
      '%entity_name' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
