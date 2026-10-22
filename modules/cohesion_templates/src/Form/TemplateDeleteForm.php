<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\Form\CohesionDeleteForm;

/**
 * Class TemplateDeleteForm.
 *
 * Builds the form to delete Site Studio custom styles entities.
 *
 * @package Drupal\cohesion_templates\Form
 */
class TemplateDeleteForm extends CohesionDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {

    if ($this->entity->getEntityTypeId() === 'cohesion_content_templates' && $this->entity->get('view_mode') !== 'full') {
      return $this->t('Are you sure you want to reset the @entity_type %entity_name?', [
        '@entity_type' => strtolower($this->entity->getEntityType()->get('label_singular')),
        '%entity_name' => $this->entity->label(),
      ]);
    }

    return parent::getQuestion();
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    if ($this->entity->getEntityTypeId() === 'cohesion_content_templates' && $this->entity->get('view_mode') !== 'full') {
      return $this->t('Reset');
    }

    return parent::getConfirmText();
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {

    $description = $this->t('Deleting a @entity_type will stop it from being used and delete the configuration of your @entity_type. This action cannot be undone.', [
      '@entity_type' => ucfirst(strtolower($this->entity->getEntityType()->get('label_singular'))),
    ]);

    if ($this->entity->getEntityTypeId() === 'cohesion_content_templates' && $this->entity->get('view_mode') !== 'full') {
      $description = $this->t('Resetting a @entity_type will stop it from being used and delete the configuration of your @entity_type. This action cannot be undone.', [
        '@entity_type' => ucfirst(strtolower($this->entity->getEntityType()->get('label_singular'))),
      ]);
    }

    return $description;
  }

}
