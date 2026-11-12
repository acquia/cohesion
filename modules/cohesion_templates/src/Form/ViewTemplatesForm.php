<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\TemplateStorage\TemplateStorageBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * View templates form.
 *
 * @package Drupal\cohesion_templates\Form
 */
class ViewTemplatesForm extends TemplateForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // Attach tokens related to views.
    $form['cohesion']['#token_browser'] = 'view';

    $entity = $this->entity;

    $form_class = str_replace('_', '-', $entity->getEntityTypeId()) . '-' . str_replace('_', '-', $entity->id() ?? '') . '-form';
    $form['#attributes']['class'][] = $form_class;

    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
      '#access' => TRUE,
      '#weight' => 1,
      '#description' => $this->entity->getEntityMachineNamePrefix(),
      '#description_display' => 'before',
      '#default_value' => str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? ''),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => t('Machine name'),
        'replace_pattern' => '[^a-z0-9\_]+',
        'replace' => '_',
        'field_prefix' => $this->entity->getEntityMachineNamePrefix(),
        'exists' => [$this, 'checkUniqueMachineName'],
        'entity_type_id' => $this->entity->getEntityTypeId(),
        'entity_id' => $this->entity->id(),
      ],
      '#disabled' => !$this->entity->canEditMachineName(),
    ];

    return $form;
  }

  /**
   * Save the View template and set status/modified.
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    $entity = $this->entity;

    // Set ID and custom flag if adding a custom template.
    $this->setEntityIdFromForm($entity, $form_state);

    // Global template uses existing view mode template suggestion name, whereas
    // node_type specific templates use custom prefix and suggestion.
    $filename_prefix = 'views-view' . TemplateStorageBase::TEMPLATE_PREFIX;
    $filename = $filename_prefix . str_replace('_', '-', $entity->get('id'));
    $entity->set('twig_template', $filename);
    // Set custom.
    $entity->set('custom', TRUE);
    return parent::save($form, $form_state);
  }

}
