<?php

namespace Drupal\cohesion_website_settings\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class ColorPaletteEditForm.
 *
 * A form that allows users to edit multiple colors on a single page.
 *
 * @package Drupal\cohesion_website_settings\Form
 */
class ColorPaletteEditForm extends WebsiteSettingsGroupFormBase {

  const ENTITY_TYPE = 'cohesion_color';

  const FORM_TITLE = 'color palette';

  const FORM_ID = 'website_settings_color_palette_form';

  const FORM_CLASS = 'cohesion-website-settings-color-palette-form';

  const COH_FROM_ID = 'color_palette';

  const PLUGIN_ID = 'color_entity_groups';

  /**
   * {@inheritdoc}
   */
  protected function stepOneSubmit(array &$form, FormStateInterface $form_state) {
    // Fetch and decode the color palette JSON blob.
    $colors = json_decode($form_state->getValue('json_values'));
    if ($colors->colors !== NULL) {
      [$this->in_use_list, $this->changed_entities] = $this->getEntityGroupsPlugin()->saveFromModel($colors);

      // (Optionally) run color rebuild batch for entities using changed colors.
      if (count($this->in_use_list)) {

        // The plugin cannoy be serialized across pages, so needs clearing.
        $this->entityGroupsPlugin = NULL;

        // Move to step #2 in the form.
        $this->step++;
        $form_state->setRebuild();
      }
      else {
        // No need to run the batch, so just save any entities and show message.
        /** @var \Drupal\cohesion_website_settings\Entity\Color $color_entity */
        foreach ($this->changed_entities as $color_entity) {
          $color_entity->save();
        }

        \Drupal::messenger()->addMessage($this->t('The color palette has been updated.'));
      }
    }
    // Json data was corrupt.
    else {
      \Drupal::messenger()->addError($this->t('There was an error saving the color palette. The form data was invalid or corrupt.'));
    }
  }

  /**
   * @param array $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {

    // Check that we don't try to validate when submitting after a rebuild.
    if ($this->step !== 2) {
      $form_values = json_decode($form_state->getValues()['json_values'])->colors;

      $values = [];
      foreach ($form_values as $key => $form_value) {
        if (!in_array($form_value->uid, $values)) {
          $values[$key] = $form_value->uid;

        }
        else {
          $form_state->setErrorByName('cohesion', $this->t('The color label must be a unique value.'));
        }
      }
    }
  }

}
