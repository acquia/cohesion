<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Builds the form to enable a cohesion entity.
 */
class CohesionEnableSelectionForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to enable selection of the @entity_type %entity_name?', [
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
  public function getDescription() {
    return $this->t('Enabling selection of your %entity_type will include it in the %entity_type select drop-down.', [
      '%entity_type' => ucfirst(strtolower($this->entity->getEntityType()->get('label_singular'))),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Enable selection');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->set('selectable', TRUE);
    $this->entity->save();

    \Drupal::messenger()->addMessage($this->t('Selection for %entity_name successfully enabled.', [
      '%entity_name' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
