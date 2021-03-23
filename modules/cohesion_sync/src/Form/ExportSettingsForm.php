<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class ExportSettingsForm.
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
    $form['help'] = [
      '#markup' => '<p>' . $this->t('Define the entity types that should be included when performing a full export.') . '</p>',
      '#weight' => 1,
    ];

    // Build the tableselect of entities.
    $options = [];

    $definitions = $this->entityTypeManager->getDefinitions();
    ksort($definitions);

    foreach ($this->usagePluginManager->getDefinitions() as $item) {
      if ($item['exportable']) {
        try {
          $options[$item['entity_type']] = [
            'name' => ucfirst($this->entityTypeManager->getDefinition($item['entity_type'])->getPluralLabel()->__toString()),
          ];
        }
        catch (\Throwable $e) {
          continue;
        }
      }
    }

    ksort($options);

    $form['enabled_entity_types'] = [
      '#type' => 'tableselect',
      '#header' => [
        'name' => 'Entity type',
      ],
      '#options' => $options,
      '#empty' => $this->t('No Site Studio entity types found.'),
      '#default_value' => $this->configSyncSettings->get('enabled_entity_types') ? $this->configSyncSettings->get('enabled_entity_types') : [],
      '#weight' => 2,
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
    $this->configSyncSettings->save();

    \Drupal::messenger()->addMessage($this->t('Entity access has been updated.'));
  }

}
