<?php

namespace Drupal\cohesion_website_settings\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class SCSSVariablesEditForm.
 *
 * A form that allows users to edit SCSS variables on a single page.
 *
 * @package Drupal\cohesion_website_settings\Form
 */
class SCSSVariablesEditForm extends WebsiteSettingsGroupFormBase {

  const ENTITY_TYPE = 'cohesion_scss_variable';

  const FORM_TITLE = 'SCSS variables';

  const FORM_ID = 'website_settings_scss_variables_form';

  const FORM_CLASS = 'cohesion-website-settings-scss-variables-form';

  const COH_FROM_ID = 'scss_variables';

  const PLUGIN_ID = 'scss_variable_entity_groups';

  /**
   * {@inheritdoc}
   */
  protected function stepOneSubmit(array &$form, FormStateInterface $form_state) {
    // Fetch and decode the SCSS variables JSON blob.
    $variables = json_decode($form_state->getValue('json_values'));

    if (is_array($variables->SCSSVariables)) {
      [$this->in_use_list, $this->changed_entities] = $this->getEntityGroupsPlugin()->saveFromModel($variables);

      // (Optionally) run SCSS variables rebuild batch for entities using
      // changed SCSS variables.
      if (count($this->in_use_list)) {

        // The plugin cannot be serialized across pages, so needs clearing.
        $this->entityGroupsPlugin = NULL;

        // Move to step #2 in the form.
        $this->step++;
        $form_state->setRebuild();
      }
      else {
        // No need to run the batch, so just save any entities and show message.
        /** @var \Drupal\cohesion_website_settings\Entity\SCSSVariable $variable_entity */
        foreach ($this->changed_entities as $variable_entity) {
          $variable_entity->save();
        }

        \Drupal::messenger()->addMessage($this->t('The SCSS variables have been updated.'));
      }
    }
    // Json data was corrupt.
    else {
      \Drupal::messenger()->addError($this->t('There was an error saving the SCSS variables. The form data was invalid or corrupt.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Check that we don't try to validate when submitting after a rebuild.
    if ($this->step !== 2) {
      $form_values = json_decode($form_state->getValues()['json_values'])->SCSSVariables;

      $values = [];
      foreach ($form_values as $key => $form_value) {
        if (!in_array($form_value->uid, $values)) {
          $values[$key] = $form_value->uid;

        }
        else {
          $form_state->setErrorByName('cohesion', $this->t('The SCSS variable name must be a unique value.'));
        }
      }
    }
  }

}
