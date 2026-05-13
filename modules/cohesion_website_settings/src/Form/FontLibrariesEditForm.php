<?php

namespace Drupal\cohesion_website_settings\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class FontLibrariesEditForm.
 *
 * A form that allows users to edit multiple font libraries and font stacks on
 * a single page.
 *
 * @package Drupal\cohesion_website_settings\Form
 */
class FontLibrariesEditForm extends WebsiteSettingsGroupFormBase {

  const ENTITY_TYPE = 'cohesion_font_library';

  const FORM_TITLE = 'font libraries';

  const FORM_ID = 'website_settings_font_libraries_form';

  const FORM_CLASS = 'cohesion-website-settings-font-libraries-form';

  const COH_FROM_ID = 'font_libraries';

  const PLUGIN_ID = 'font_libraries_entity_groups';

  /**
   * {@inheritdoc}
   */
  protected function stepOneSubmit(array &$form, FormStateInterface $form_state) {
    // Fetch and decode the libraries JSON blob.
    $libraries = json_decode($form_state->getValue('json_values'));

    if (is_array($libraries->fonts)) {
      [$this->in_use_list, $this->changed_entities, $flush_caches] = $this->getEntityGroupsPlugin()->saveFromModel($libraries);

      // (Optionally) run color rebuild batch for entities using changed colors.
      if (count($this->in_use_list)) {

        // The plugin cannot be serialized across pages, so needs clearing.
        $this->entityGroupsPlugin = NULL;

        $this->step++;
        $form_state->setRebuild();
      }
      else {
        // Flush the render cache without a rebuild?
        if ($flush_caches) {
          $renderCache = \Drupal::service('cache.render');
          $renderCache->invalidateAll();
        }

        // No need to run the batch, so just save any entities and show message.
        foreach ($this->changed_entities as $font_entity) {
          $font_entity->save();
        }

        \Drupal::messenger()->addMessage($this->t('The font libraries have been updated.'));
      }
    }
    // Json data was corrupt.
    else {
      \Drupal::messenger()->addError($this->t('There was an error saving the font libraries. The form data was invalid or corrupt.'));
    }
  }

}
