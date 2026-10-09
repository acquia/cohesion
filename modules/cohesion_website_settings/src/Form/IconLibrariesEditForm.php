<?php

namespace Drupal\cohesion_website_settings\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class IconLibrariesEditForm.
 *
 * A form that allows users to edit multiple icon libraries on a single page.
 *
 * @package Drupal\cohesion_website_settings\Form
 */
class IconLibrariesEditForm extends WebsiteSettingsGroupFormBase {

  const ENTITY_TYPE = 'cohesion_icon_library';

  const FORM_TITLE = 'icon libraries';

  const FORM_ID = 'website_settings_icon_libraries_form';

  const FORM_CLASS = 'cohesion-website-settings-icon-libraries-form';

  const COH_FROM_ID = 'icon_libraries';

  const PLUGIN_ID = 'icon_libraries_entity_groups';

  /**
   * {@inheritdoc}
   */
  protected function stepOneSubmit(array &$form, FormStateInterface $form_state) {
    // Fetch and decode the libraries JSON blob.
    $libraries = json_decode($form_state->getValue('json_values'));

    if (isset($libraries->iconLibraries)) {
      [$this->in_use_list, $this->changed_entities] = $this->getEntityGroupsPlugin()->saveFromModel($libraries);

      // (Optionally) run color rebuild batch for entities using changed colors.
      if (count($this->in_use_list)) {

        // The plugin cannot be serialized across pages, so needs clearing.
        $this->entityGroupsPlugin = NULL;

        $this->step++;
        $form_state->setRebuild();
      }
      else {
        // No need to run the batch, so just save any entities and show message.
        /** @var \Drupal\cohesion_website_settings\Entity\IconLibrary $icon_library_entity */
        foreach ($this->changed_entities as $icon_library_entity) {
          $icon_library_entity->save();
        }

        \Drupal::messenger()->addMessage($this->t('The icon libraries have been updated.'));
      }
    }
    // Json data was corrupt.
    else {
      \Drupal::messenger()->addError($this->t('There was an error saving the icon libraries. The form data was invalid or corrupt.'));
    }
  }

}
