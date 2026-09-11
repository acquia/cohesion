<?php

namespace Drupal\cohesion_website_settings\Form;

use Drupal\cohesion\Form\CohesionBaseForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Website settings form.
 *
 * @package Drupal\cohesion_website_settings\Form
 */
class WebsiteSettingsForm extends CohesionBaseForm {

  /**
   * @var int
   */
  protected $step = 1;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {

    if ($this->step == 1) {
      $form = parent::form($form, $form_state);

      // Title field should be disabled on website settings.
      $form['details']['#attributes']['class'][] = 'hidden';
      $form['details']['#type'] = 'container';
      $form['details']['label']['#attributes']['disabled'] = 'disabled';
    }
    else {

      // Set page title and warning (base_unit_settings only).
      $form = [];
      $form['#title'] = $this->t('Are you sure you want to update the %name?', ['%name' => $this->entity->label()]);

      $form['markup'] = [
        '#markup' => t('You are about to change core website settings. This will rebuild styles and templates.'),
      ];
    }

    // Attach the SCSS variables to this form.
    $scss_variable_values = [];

    /** @var \Drupal\cohesion_website_settings\Entity\SCSSVariable $scss_variable_entity */
    foreach ($this->entityTypeManager->getStorage('cohesion_scss_variable')->loadMultiple() as $scss_variable_entity) {
      $values = $scss_variable_entity->getDecodedJsonValues();
      $scss_variable_values[$values['uid']] = $values['value'];
    }

    $form['#attached']['drupalSettings']['cohesion']['scss_variables'] = $scss_variable_values;

    // And finish.
    return $form;
  }

  /**
   * Customise website settings form actions.
   *
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *
   * @return mixed
   */
  public function actions(array $form, FormStateInterface $form_state) {
    $actions = parent::actions($form, $form_state);

    if ($this->step > 1) {

      // Add a "Reset" button.
      $actions['cancel'] = $actions['submit'];
      $actions['cancel']['#value'] = t('Cancel');
      $actions['cancel']['#type_value'] = 'cancel';
      $actions['cancel']['#access'] = TRUE;
      $actions['cancel']['#weight'] = 10;

      // Add a "Save" button.
      $actions['rebuild'] = $actions['submit'];
      $actions['rebuild']['#value'] = t('Rebuild');
      $actions['rebuild']['#type_value'] = 'rebuild';
      $actions['rebuild']['#access'] = TRUE;
      $actions['rebuild']['#weight'] = 10;
      $actions['rebuild']['#attributes']['class'][] = 'button--primary';

      unset($actions['enable']);
    }
    else {
      $actions['enable']['#type_value'] = 'save';
      $actions['enable']['#attributes']['class'][] = 'button--primary';
      // Only one button so remove drop button.
      unset($actions['enable']['#dropbutton']);
    }
    unset($actions['continue']);

    return $actions;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state, $redirect = NULL) {
    $triggeringElement = $form_state->getTriggeringElement();
    $status = '';

    if ($this->step == 1) {
      $this->step = 2;
      $form_state->setRebuild();
    }
    elseif ($triggeringElement['#type_value'] == 'rebuild') {
      $redirect = Url::fromRoute('cohesion_website_settings.batch_reload');

      // Only save the entity if user clicked "Save" button $op.
      $status = parent::save($form, $form_state, $redirect);
    }
    else {
      $this->step = 1;
      $form_state->setRebuild();
    }
    return $status;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $triggeringElement = $form_state->getTriggeringElement();

    // If reset - show the confirmation page.
    if (isset($triggeringElement['#type_value']) && $triggeringElement['#type_value'] === 'save') {
      return;
    }
    // If cancel, just redirect back to the collection.
    elseif (isset($triggeringElement['#type_value']) && $triggeringElement['#type_value'] == 'Cancel') {
      $form_state->setRedirectUrl($this->entity->toUrl('collection'));
    }
    // If save, then actually save the entity.
    else {
      parent::submitForm($form, $form_state);
    }
  }

}
