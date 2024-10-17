<?php

namespace Drupal\cohesion_style_helpers\Form;

use Drupal\cohesion\Form\CohesionStyleBuilderForm;
use Drupal\cohesion_custom_styles\Entity\CustomStyleType;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class ContentTemplatesForm.
 *
 * @package Drupal\cohesion_custom_styles\Form
 */
class StyleHelpersForm extends CohesionStyleBuilderForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    // Are we adding or editing an entity?
    $operation = $this->getOperation();
    $request = \Drupal::request();
    if ($operation == 'add') {
      $custom_style_type_id = $request->attributes->get('custom_style_type');
    }
    else {
      $custom_style_type_id = $this->entity->get('custom_style_type');
    }
    $custom_style_type = $this->entityTypeManager->getStorage('custom_style_type')->load($custom_style_type_id);

    // Must come after modifying the entity data.
    $form = parent::form($form, $form_state);

    if ($operation == 'add') {
      $form['#title'] = $this->t('Create %label', [
        '%label' => strtolower($custom_style_type->label()),
      ]);
    }

    // Boot angular with the given custom style type.
    $form['#attached']['drupalSettings']['cohesion']['formGroup'] = 'custom_styles';
    $form['#attached']['drupalSettings']['cohesion']['formId'] = $custom_style_type->id();
    $form['#attached']['drupalSettings']['cohOnInitForm'] = \Drupal::service('settings.endpoint.utils')->getCohFormOnInit('custom_styles', $custom_style_type->id());
    $form['#attributes']['class'][] = 'cohesion-style-builder-edit-form';

    // Show custom style type hidden from user.
    $form['details']['custom_style_type'] = [
      '#type' => 'hidden',
      '#default_value' => $custom_style_type->id(),
      '#required' => TRUE,
      '#access' => TRUE,
    ];

    // Show custom style type (read-only) for display purposes only.
    $form['details']['custom_style_type_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Style helper type'),
      '#maxlength' => 255,
      '#default_value' => $custom_style_type->label(),
      '#disabled' => TRUE,
      '#required' => TRUE,
      '#access' => TRUE,
      '#weight' => 1,
    ];

    // Machine name generated from the label.
    $form['details']['machine_name'] = [
      '#title' => $this->t('Machine name'),
      '#maxlength' => 32 - strlen($this->entity->getEntityMachineNamePrefix()),
      '#access' => TRUE,
      '#weight' => 0,
      '#description' => $this->entity->getEntityMachineNamePrefix(),
      '#description_display' => 'before',
      '#default_value' => str_replace($this->entity->getEntityMachineNamePrefix(), '', $this->entity->id() ?? ''),
      '#type' => 'ajax_machine_name',
      '#required' => FALSE,
      '#machine_name' => [
        'source' => ['details', 'label'],
        'label' => $this->t('Machine name'),
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
   * Validate the Content template form.
   *
   * @inheritdoc
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    // Make sure the sent custom style type exists as a CustomStyleType config
    // entity.
    $custom_style_type = $form_state->getValue('custom_style_type');
    $custom_style_types = CustomStyleType::loadMultiple();

    if (!array_key_exists($custom_style_type, $custom_style_types)) {
      $form_state->setErrorByName('custom_style_type', $this->t("The custom style type is invalid."));
    }

    // Note, the machine name check is performed automatically in
    // cohesion_custom_styles.module =>
    // _cohesion_custom_styles_check_machine_name()
    // Check if the machine name is empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }

    // Note, the machine name check is performed automatically in
    // $this->>checkUniqueMachineName()
  }

  /**
   * Save the Content template and set status/modified.
   *
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {

    $entity = $this->entity;
    $op = $this->getOperation();

    // Set ID and custom flag if adding a custom template.
    $this->setEntityIdFromForm($entity, $form_state);

    return parent::save($form, $form_state);
  }

  /**
   * Required by machine name field validation.
   *
   * @param $value
   *
   * @return bool
   */
  public function exists($value) {
    return FALSE;
  }

}
