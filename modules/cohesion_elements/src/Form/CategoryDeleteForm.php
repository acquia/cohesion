<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Category delete form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class CategoryDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Deleting a <em>category</em> will delete it’s configuration. This action cannot be undone.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $category_entity_class = $this->entity->getEntityType()->getOriginalClass();

    $category_entity_id = $this->entity->id();
    $category_entity_type_id = $this->entity->getEntityTypeId();

    // First, delete the category.
    // Use reset instead of delete because some entities like BaseStyle or
    // ContentTemplates should never be deleted but we don't want to override
    // the default delete behavior in case we do have to delete it.
    $this->entity->reset();

    // Set all the components / helpers that use this category to
    // "Uncategorized".
    \Drupal::service('cohesion_elements.category_relationships')->processCategory($category_entity_id, $category_entity_type_id, $category_entity_class);

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
