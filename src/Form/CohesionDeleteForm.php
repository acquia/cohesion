<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionDeleteForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the @entity_type %entity_name?', [
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
    return $this->t('Delete');
  }

  /**
   * Delete the entity (and any child entities related via the "children"
   * field).
   * {@inheritdoc}.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $form_state->setRedirectUrl($this->getCancelUrl());

    // Use reset instead of delete because some entities like BaseStyle or
    // ContentTemplates should never be deleted but we don't want to override
    // the default delete behavior in case we do have to delete it.
    $this->entity->reset();

    \Drupal::messenger()->addMessage($this->t('%entity_name successfully deleted.', [
      '%entity_name' => $this->entity->label(),
    ]));
  }

}
