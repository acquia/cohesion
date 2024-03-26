<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\Core\Form\FormStateInterface;

/**
 * Component form.
 *
 * @package Drupal\cohesion_elements\Form
 */
class ComponentForm extends ElementBaseForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $form['details']['#summary_attributes'] = ['data-ssa-help-link' => \Drupal::service('cohesion.support_url')->getSupportUrlPrefix() . 'component-form-builder-details'];
    $form['details']['#attached']['library'][] = 'cohesion/cohesion-accordion-element';

    $form_state->setCached(FALSE);
    // Tell Angular that this is a component sidebar.
    $form['#attached']['drupalSettings']['cohesion']['isComponentForm'] = TRUE;
    if ($this->moduleHandler->moduleExists('tmgmt')) {
      $form['#attached']['drupalSettings']['cohesion']['tmgmt'] = TRUE;
    }
    return $form;
  }

  /**
   * Validate the Element form.
   *
   * @inheritdoc
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);

    if ($json_values = $form_state->getValue('json_values')) {
      $canvasInstance = new LayoutCanvas($form_state->getValue('json_values'));

      $machine_names = [];
      $undefined_machines_names = [];
      foreach ($canvasInstance->iterateModels('component_form') as $model) {
        if ($model->getElement()->getProperty(['type']) == 'form-field') {

          $machine_name = $model->getProperty(['settings', 'machineName']);
          $element_title = $model->getProperty(['settings', 'title']);
          if ($machine_name == '') {
            $undefined_machines_names[$model->getUUID()] = $element_title;
          }
          else {
            $machine_names[$machine_name][$model->getUUID()] = $element_title;
          }

        }
      }

      $error_count = 0;
      $layout_canvas_error = [];
      if (!empty($undefined_machines_names)) {
        $error_count++;
        $form_state->setErrorByName('cohesion_' . $error_count, $this->t('Undefined machine name(s). Please make sure to define a machine name for these form elements: %machine_names', ['%machine_names' => implode(', ', $undefined_machines_names)]));
        $layout_canvas_error = array_merge($layout_canvas_error, array_keys($undefined_machines_names));
      }

      foreach ($machine_names as $element_machine_name) {
        if (count($element_machine_name) > 1) {
          $error_count++;
          $form_state->setErrorByName('cohesion_' . $error_count, $this->t('Duplicate machine names. Please make sure to define unique machine names form these elements: %machine_names', ['%machine_names' => implode(', ', $element_machine_name)]));
          $layout_canvas_error = array_merge($layout_canvas_error, array_keys($element_machine_name));
        }
      }
    }

    if (!empty($layout_canvas_error)) {
      $form['#attached']['drupalSettings']['cohesion']['layout_canvas_errors'] = $layout_canvas_error;
    }

    // Check if the machine name is empty.
    if (empty($form_state->getValue('machine_name'))) {
      $form_state->setErrorByName('machine_name', $this->t('The machine name cannot be empty.'));
    }
  }

}
