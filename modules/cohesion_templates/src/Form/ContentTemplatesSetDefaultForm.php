<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Class ContentTemplatesSetDefaultForm.
 *
 * Builds the form to confirm settings content template as default.
 *
 * @package Drupal\cohesion_templates\Form
 */
class ContentTemplatesSetDefaultForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    $template_type = $this->entity->get('entity_type') == '__any__' ? 'Global' : ucwords($this->entity->get('entity_type'));
    return $this->t('Are you sure you want to set %template_name as the default %template_type content template?', [
      '%template_type' => $template_type,
      '%template_name' => $this->entity->label(),
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return new Url('entity.cohesion_content_templates.collection', ['content_entity_type' => $this->entity->get('entity_type')]);
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Set as Default');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return $this->t('Setting a <em>Content template</em> as default will apply the <em>Content template</em> to all entities except those where a more specific <em>Content template</em> has been selected.');
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $entity->setDefault();
    $entity->save();

    $template_type = $this->entity->get('entity_type') == '__any__' ? 'Global' : ucwords($this->entity->get('entity_type'));
    \Drupal::messenger()->addMessage($this->t('%template_type content template %template_name has been set as default.', [
      '%template_type' => $template_type,
      '%template_name' => $this->entity->label(),
    ]));

    $form_state->setRedirectUrl($this->getCancelUrl());
  }

}
