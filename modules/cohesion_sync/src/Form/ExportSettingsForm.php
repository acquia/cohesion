<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Export settings form.
 *
 * @package Drupal\cohesion_sync\Form
 */
class ExportSettingsForm extends ExportFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_sync_export_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'cohesion.sync.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $config_type = NULL, $config_name = NULL) {

    $form['entity_type_settings'] = [
      '#type' => 'details',
      '#title' => $this
        ->t('Entity Type Settings'),
      '#open' => TRUE,
    ];

    $form['entity_type_settings']['help'] = [
      '#markup' => '<p>' . $this->t('Define the entity types that should be included when performing a full export.') . '</p>',
      '#weight' => 1,
    ];

    $options = $this->usagePluginManager->getExportableEntities();
    ksort($options);

    $form['entity_type_settings']['enabled_entity_types'] = [
      '#type' => 'tableselect',
      '#header' => [
        'name' => 'Entity type',
      ],
      '#options' => $options,
      '#empty' => $this->t('No Site Studio entity types found.'),
      '#default_value' => $this->configSyncSettings->get('enabled_entity_types') ? $this->configSyncSettings->get('enabled_entity_types') : [],
      '#weight' => 2,
    ];

    $form['export_batch_settings'] = [
      '#type' => 'details',
      '#title' => $this
        ->t('Export Batch Settings'),
      '#open' => TRUE,
      '#description' => $this->t('Settings in this section only affect "Full Package Export" in Drupal UI. Drush command for Package export do not use Batch API therefore settings in this section has no effect.'),
    ];
    $form['export_batch_settings']['package_export_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum number of items to process per individual package export batch'),
      '#min' => '1',
      '#default_value' => $this->configSyncSettings->get('package_export_limit') ?? 10,
    ];
    $form['export_batch_settings']['full_export_limit'] = [
      '#type' => 'number',
      '#title' => $this->t('Maximum number of items to process per full package export batch'),
      '#min' => '1',
      '#default_value' => $this->configSyncSettings->get('full_export_limit') ?? 10,
    ];

    // Add the save button.
    $form['actions'] = [
      '#type' => 'actions',
      'save' => [
        '#type' => 'submit',
        '#value' => $this->t('Save configuration'),
        '#button_type' => 'primary',
        '#weight' => 4,
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->configSyncSettings->set('enabled_entity_types', $form_state->getValue('enabled_entity_types'));
    $this->configSyncSettings->set('package_export_limit', $form_state->getValue('package_export_limit'));
    $this->configSyncSettings->set('full_export_limit', $form_state->getValue('full_export_limit'));
    $this->configSyncSettings->save();

    \Drupal::messenger()->addMessage($this->t('Entity access has been updated.'));
  }

}
