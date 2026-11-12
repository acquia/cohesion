<?php

namespace Drupal\cohesion_base_styles\Form;

use Drupal\cohesion\Form\CohesionStyleBuilderForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class CohesionBaseForm.
 *
 * @package Drupal\cohesion\Form
 */
class BaseStylesForm extends CohesionStyleBuilderForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $operation = $this->getOperation();
    /** @var \Drupal\cohesion_base_styles\Entity\BaseStyles $entity */
    $entity = $this->entity;

    // If editing a base style for the first time then simulate a create.
    if ($operation == 'edit' && !$entity->isModified()) {
      $form['#title'] = $this->t('Create %label', [
        '%label' => strtolower($this->entity->label()),
      ]);

    }

    // If the base style as already been saved or is new.
    $form['#attached']['drupalSettings']['cohesion']['formGroup'] = 'base_styles';
    $form['#attached']['drupalSettings']['cohesion']['formId'] = $entity->id() && !strstr($entity->id(), 'base_') ? $entity->id() : 'generic';

    $form['#attached']['drupalSettings']['cohOnInitForm'] = \Drupal::service('settings.endpoint.utils')
      ->getCohFormOnInit($form['#attached']['drupalSettings']['cohesion']['formGroup'], $form['#attached']['drupalSettings']['cohesion']['formId']);

    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
      '#access' => TRUE,
      '#weight' => 1,
      '#description' => $entity->getEntityMachineNamePrefix(),
      '#description_display' => 'before',
      '#default_value' => str_replace($entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? ''),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => t('Machine name'),
        'replace_pattern' => '[^a-z0-9\_]+',
        'replace' => '_',
        'field_prefix' => $entity->getEntityMachineNamePrefix(),
        'exists' => [$this, 'checkUniqueMachineName'],
        'entity_type_id' => $this->entity->getEntityTypeId(),
        'entity_id' => $this->entity->id(),
      ],
      '#disabled' => !$this->entity->canEditMachineName(),
    ];

    return $form;
  }

  /**
   * Save the Content template and set status/modified.
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    $entity = $this->entity;
    $op = $this->getOperation();

    $this->setEntityIdFromForm($entity, $form_state);

    return parent::save($form, $form_state);
  }

}
