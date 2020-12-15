<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion_sync\PackagerManager;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for importing a single configuration file.
 *
 * @internal
 */
class ImportFileForm extends FormBase {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * If the config exists, this is that object. Otherwise, FALSE.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\Entity\ConfigEntityInterface|bool
   */
  protected $configExists = FALSE;

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * Validation throws up entities that need action (overwrite or keep).
   *
   * @var array
   */
  public $action_data = [];

  /**
   * The entities that would loss data if the package is imported.
   *
   * @var array
   */
  public $broken_entities = [];

  /**
   * @var int
   */
  public $step = 0;

  /**
   * @var mixed
   */
  public $file_uri;

  /**
   * ImportForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\cohesion_sync\PackagerManager $packager_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, PackagerManager $packager_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->packagerManager = $packager_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('entity_type.manager'), $container->get('cohesion_sync.packager'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'dx8_sync_import_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['help'] = [
      '#markup' => $this->t('Import an Site Studio package from a file uploaded from your local device.'),
    ];

    // Step 1: Upload file.
    if ($this->step == 0) {
      $form['package_yaml'] = [
        '#type' => 'chunked_file',
        '#title' => $this->t('Upload your *.package.yml or *.package.yml_ file'),
        '#upload_validators' => [
          'file_validate_extensions' => ['yaml yml yml_'],
        ],
      ];
    }

    // User needs to take some action.
    if ($this->step == 1) {
      $form['information'] = [
        '#markup' => '<p>' . $this->t('There are differences between your local entities and the entities you are trying to import.') . '</p>',
      ];

      $form['entry_actions']['indexes'] = [
        '#type' => 'table',
        '#header' => [
          ['data' => $this->t('Entity')],
          ['data' => $this->t('Entity type')],
          ['data' => $this->t('Action')],
        ],
      ];

      if (!empty($this->broken_entities)) {
        $form['entry_actions']['indexes']['#header'][] = ['data' => $this->t('Warning')];
      }

      foreach ($this->action_data as $uuid => $action_data_entry) {
        // If the entry requires user input.
        if ($action_data_entry['entry_action_state'] === ENTRY_EXISTING_ASK) {
          $form['entry_actions']['indexes'][$uuid]['entity_label'] = [
            '#markup' => $action_data_entry['entity_label'],
          ];

          $form['entry_actions']['indexes'][$uuid]['entity_type_label'] = [
            '#markup' => $action_data_entry['entity_type_label'],
          ];

          $form['entry_actions']['indexes'][$uuid]['action'] = [
            '#type' => 'select',
            '#options' => [
              TRUE => t('Overwrite existing'),
              FALSE => t('Keep existing'),
            ],
          ];

          if (!empty($this->broken_entities)) {
            $this->messenger()->addWarning($this->t('Some entities you are importing are missing populated fields, this will result in a loss of content. Please check the warnings listed below.'));
          }

          $warning_markup = [];
          if (isset($this->broken_entities[$action_data_entry['entry_uuid']])) {
            $form['entry_actions']['indexes'][$uuid]['#attributes'] = ['class' => 'color-warning'];
            $broken_entity = $this->broken_entities[$action_data_entry['entry_uuid']];
            $warning_markup[] = [
              '#markup' => $this->t('This entity is missing populated fields. If you choose to <strong>Overwrite existing</strong>, content in these fields will be lost.'),
            ];
            $warning_markup[] = [
              '#markup' => '<br />' . $this->formatPlural(count($broken_entity['entities']), '1 entity affected.', '@count entities affected.'),
            ];

            $warning_markup[] = [
              '#type' => 'link',
              '#title' => ' ' . $this->t('See where this entity is in use.'),
              '#url' => $broken_entity['entity']->toUrl('in-use'),
              '#options' => [
                'attributes' => [
                  'class' => ['use-ajax'],
                  'data-dialog-type' => 'modal',
                  'data-dialog-options' => Json::encode([
                    'width' => 700,
                  ]),
                ],
              ],
              '#attached' => ['library' => ['core/drupal.dialog.ajax']],
            ];

          }

          if (!empty($this->broken_entities)) {
            $form['entry_actions']['indexes'][$uuid]['warning'] = $warning_markup;
          }
        }
      }
    }

    // Standard actions for all steps.
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->t('Import'),
        '#button_type' => 'primary',
        // Don't disable the button if validation has completed otherwise the form won't submit.
        '#disabled' => !$form_state->isValidationComplete(),
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'package_yaml_upload_button') {
      // Get the uploaded file entity.
      $file_id = $form_state->getUserInput()['files']['package_yaml'];
      if ($file_entity = $this->entityTypeManager->getStorage('file')
        ->load(trim($file_id))) {
        $this->file_uri = $file_entity->getFileUri();

        try {
          // Validate the stream.
          $this->action_data = $this->packagerManager->validateYamlPackageStream($this->file_uri);
          $this->broken_entities = $this->packagerManager->validateYamlPackageContentIntegrity($this->file_uri);
        }
        catch (\Exception $e) {
          $form_state->setErrorByName('package_yaml', $this->t('The validation failed with the following message: %message', ['%message' => $e->getMessage()]));
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // User needs to take some action.
    $needs_action_response = FALSE;
    foreach ($this->action_data as $action_data_entry) {
      if ($action_data_entry['entry_action_state'] === ENTRY_EXISTING_ASK) {
        $needs_action_response = TRUE;
        break;
      }
    }

    if ($this->step == 0 && $needs_action_response) {
      $this->step = 1;
      $form_state->setRebuild();
      return TRUE;
    }

    // Remove action entries depending on user input.
    if ($this->step == 1) {
      foreach ($form_state->getValue('indexes') as $uuid => $item) {
        if ($item['action'] == FALSE) {
          $this->action_data[$uuid]['entry_action_state'] = ENTRY_EXISTING_IGNORED;
        }
        else {
          $this->action_data[$uuid]['entry_action_state'] = ENTRY_EXISTING_OVERWRITTEN;
        }
      }
    }

    // Check to see if there is anything to do.
    if (!count($this->action_data)) {
      \Drupal::messenger()->addMessage($this->t('There were no changes detected. Nothing was applied.'));
      return TRUE;
    }

    // Apply all the items from the import.
    try {
      $this->packagerManager->applyBatchYamlPackageStream($this->file_uri, $this->action_data);
    }
    catch (\Exception $e) {
      \Drupal::messenger()->addError($this->t('The import failed with the following message: %message', ['%message' => $e->getMessage()]));
      return FALSE;
    }

  }

}
