<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionDisableSelectionForm.
 *
 * Builds the form to enable a cohesion entity.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionDisableSelectionForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to disable selection of the @entity_type %entity_name?', [
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
    return $this->t('Disable selection');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Disabling selection of your @entity_type will remove it from the @entity_type select drop-down to prevent further selection.
     All instances where it’s been selected will not be affected.', [
       '@entity_type' => ucfirst(strtolower($this->entity->getEntityType()->get('label_singular'))),
     ]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->entity->set('selectable', FALSE);
    $this->entity->save();

    \Drupal::messenger()->addMessage($this->t('Selection for %entity_name successfully disable.', [
      '%entity_name' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
