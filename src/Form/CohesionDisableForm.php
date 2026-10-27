<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionDisableForm.
 *
 * Builds the form to disable custom style.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionDisableForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to disable the @entity_type %entity_name?', [
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
    return $this->t('Disable');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->disable();
    $this->entity->save();

    \Drupal::messenger()->addMessage($this->t('%entity_name successfully disabled.', [
      '%entity_name' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
