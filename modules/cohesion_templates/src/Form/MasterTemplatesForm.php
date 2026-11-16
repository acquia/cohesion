<?php

namespace Drupal\cohesion_templates\Form;

use Drupal\cohesion\TemplateStorage\TemplateStorageBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Master templates form.
 *
 * @package Drupal\cohesion_templates\Form
 */
class MasterTemplatesForm extends TemplateForm {

  /**
   * Customise the Master Template edit form.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return array
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    // If user is "creating" the master template, the title needs to reflect
    // this.
    $operation = $this->getOperation();
    if ($operation == 'duplicate') {
      $this->entity->set('default', FALSE);
    }

    $form['set_default'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Set as default'),
      '#default_value' => ($this->entity->get('default')) ? TRUE : FALSE,
      '#weight' => 30,
    ];

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
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    $entity = $this->entity;

    // Set ID and custom flag if adding a custom template.
    $this->setEntityIdFromForm($entity, $form_state);

    // Global template uses existing view mode template suggestion name, whereas
    // node_type specific templates use custom prefix and suggestion.
    $filename_prefix = 'page' . TemplateStorageBase::TEMPLATE_PREFIX . '-';
    $filename = $filename_prefix . str_replace('_', '-', $entity->get('id'));
    $entity->set('twig_template', $filename);

    if ($form_state->getValue('set_default')) {
      $this->entity->setDefault(TRUE);
    }
    else {
      $this->entity->setDefault(FALSE);
    }

    // Save.
    $status = parent::save($form, $form_state);

    // Flush drupal caches.
    drupal_flush_all_caches();
    return $status;
  }

}
