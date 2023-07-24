<?php

namespace Drupal\cohesion\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Sitestudio sidebar edit form.
 *
 * @package Drupal\cohesion\Form
 */
class CohesionSidebarEditForm extends FormBase {

  public function getFormId() {
    return 'CohesionSidebarEditForm';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['#attributes']['class'] = [
      'cohesion-component-in-context',
    ];

    $form['cohesion'] = [
      // Drupal\cohesion\Element\CohesionField.
      '#type' => 'cohesionfield',
      '#json_values' => '{}',
      '#json_mapper' => '{}',
      '#entity' => NULL,
      '#classes' => ['cohesion-component-in-context'],
      '#cohFormGroup' => 'frontend_edit',
      '#cohFormId' => 'component',
      '#isContentEntity' => TRUE,
    ];

    $form['cohesion']['#token_browser'] = 'all';
    // Add the shared attachments.
    _cohesion_shared_page_attachments($form);

    return $form;
  }

  /**
   * This is use only to display the form
   * @inheritDoc
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
