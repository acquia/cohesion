<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Tag delete form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class TagDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    if ((string) $this->entity->getInUseMarkup() !== 'Not in use') {
      return $this->t('This tag is currently in use. Deleting this tag will remove it from the components and delete its configuration. This action cannot be undone.');
    }

    return $this->t('Deleting a <em>tag</em> will delete its configuration. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    // First, delete the tag.
    // Use reset instead of delete because some entities like BaseStyle or
    // ContentTemplates should never be deleted but we don't want to override
    // the default delete behavior in case we do have to delete it.
    $this->entity->reset();

    // Redirect and message.
    $form_state->setRedirectUrl($this->getCancelUrl());

    \Drupal::messenger()->addMessage(
      $this->t('%entity_name successfully deleted.', [
        '%entity_name' => $this->entity->label(),
      ]
      )
    );
  }

}
