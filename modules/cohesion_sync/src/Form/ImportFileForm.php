<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion_sync\PackagerManager;
use Drupal\cohesion_sync\PackageSourceManager;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\TempStore\SharedTempStoreFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for importing a single configuration file.
 *
 * @internal
 */
class ImportFileForm extends FormBase {

  const EXTENSIONS = ['tar.gz', 'tgz', 'tar.bz2'];
  const FILE_EXTENSION_PATTERN = "/.+(\.tar\.gz|\.tgz|\.tar\.bz2){1}$/";

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
  public $file_uri = NULL;


  /**
   * The key to store the data needed for this form
   *
   * @var string
   */
  public $store_key;

  /**
   * The site studio temp shared store
   *
   * @var \Drupal\Core\TempStore\SharedTempStore
   */
  public $sitestudioTempSharedStore;

  /**
   * The uuid service
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * The configuration storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $fileStorage;

  /**
   * Package Source Manager service.
   *
   * @var \Drupal\cohesion_sync\PackageSourceManager
   */
  protected $packageSourceManager;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The settings object.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $cohesionSettings;

  /**
   * ImportForm constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\cohesion_sync\PackagerManager $packager_manager
   * @param \Drupal\Core\TempStore\SharedTempStoreFactory $temp_shared_store_factory
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   * @param \Drupal\Core\Config\StorageInterface $file_storage
   *   The configuration storage.
   * @param \Drupal\cohesion_sync\PackageSourceManager $package_source_manager
   * @param ?\Drupal\Core\File\FileSystemInterface $file_system
   *    The file system service.
   * @param ?\Drupal\Core\Site\Settings $settings
   *    The settings object.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    PackagerManager $packager_manager,
    SharedTempStoreFactory $temp_shared_store_factory,
    UuidInterface $uuid,
    StorageInterface $file_storage,
    PackageSourceManager $package_source_manager,
    ConfigFactoryInterface $config_factory,
    ?FileSystemInterface $file_system = NULL,
    ?Settings $settings = NULL,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->packagerManager = $packager_manager;
    $this->sitestudioTempSharedStore = $temp_shared_store_factory->get('sitestudio');
    $this->uuidGenerator = $uuid;
    $this->fileStorage = $file_storage;
    $this->packageSourceManager = $package_source_manager;
    $this->setConfigFactory($config_factory);
    $this->fileSystem = $file_system;
    $this->settings = $settings;
    $this->cohesionSettings = $this->config('cohesion.settings');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('cohesion_sync.packager'),
      $container->get('tempstore.shared'),
      $container->get('uuid'),
      $container->get('cohesion_sync.import_file_storage'),
      $container->get('cohesion_sync.package_source_manager'),
      $container->get('config.factory'),
      $container->get('file_system'),
      $container->get('settings')
    );
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
      '#markup' => $this->t('Import a Site Studio package from a file uploaded from your local device.'),
    ];

    if ($this->step == 1) {
      $form = array_merge($form, $this->buildLegacyForm($form_state));
    }
    else {

      $sync_path = _cohesion_sync_temp_directory();
      if (file_exists($sync_path)) {
        try {
          $this->fileSystem->deleteRecursive($sync_path);
        }
        catch (FileException $e) {
          // Ignore failed deletes.
        }
      }

      $directory_is_writable = $this->fileSystem->prepareDirectory($sync_path, FileSystemInterface::CREATE_DIRECTORY);
      if (!$directory_is_writable) {
        $this->messenger()->addError($this->t('The directory %directory is not writable.', ['%directory' => $sync_path]));
      }

      $form['import'] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => 'Import package',
      ];

      $form['import']['import_tarball'] = [
        '#type' => 'file',
        '#title' => $this->t('Configuration archive'),
        '#description' => $this->t('Allowed types: @extensions.', ['@extensions' => implode(" ", self::EXTENSIONS)]),
      ];

      $form['import']['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Upload'),
        '#disabled' => !$directory_is_writable,
        '#name' => 'import_submit',
      ];

      // Only show Legacy import if user has turned it on.
      if ($this->cohesionSettings->get('sync_legacy_visibility')) {
        $form['legacy_import'] = [
          '#type' => 'details',
          '#open' => FALSE,
          '#title' => 'Legacy import (single yml file)',
        ];

        $form['legacy_import'][] = $this->buildLegacyForm($form_state);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $this->validateLegacyForm($form_state);

    if ($form_state->getTriggeringElement()['#name'] == 'import_submit') {
      $all_files = $this->getRequest()->files->get('files', []);
      if (!empty($all_files['import_tarball'])) {
        /** @var \Symfony\Component\HttpFoundation\File\UploadedFile $file_upload */
        $file_upload = $all_files['import_tarball'];
        // Reject yml or yml_ uploads.
        if (preg_match(self::FILE_EXTENSION_PATTERN, $file_upload->getClientOriginalName()) !== 1 || !$file_upload->isValid()) {
          $form_state->setErrorByName('import_tarball', $this->t('Invalid file type'));
        }
        else {
          $form_state->setValue('import_tarball', $file_upload->getRealPath());
          return;
        }
      }

      $form_state->setErrorByName('import_tarball', $this->t('The file could not be uploaded.'));
    }

    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'legacy_import') {
      return $this->legacyFormSubmit($form_state);
    }
    elseif ($form_state->getTriggeringElement()['#name'] == 'import_submit'){
      if ($path = $form_state->getValue('import_tarball')) {
        $this->fileStorage->deleteAll();
        try {
          $packageSourceService = $this->packageSourceManager->getSourceService('tar_archive_package');
          $source_metadata = [
            'file_location' => $path,
          ];
          $sync_dir = $packageSourceService->preparePackage($source_metadata);

          $this->messenger()->addStatus($this->t('Your configuration files were successfully uploaded to :sync_dir and are ready for import.', [':sync_dir' => $sync_dir]));
          $form_state->setRedirect('cohesion_sync.sync');
        }
        catch (\Exception $e) {
          $this->messenger()->addError($this->t('Could not extract the contents of the tar file. The error message is <em>@message</em>', ['@message' => $e->getMessage()]));
        }
      }
    }

  }

  /**
   * Build the form for entries that needs actions
   *
   * @param $action_data_entry
   *
   * @return array
   */
  private function buildActionForm(array $action_data_entry) {
    $action_form = [];
    $action_form['entity_label'] = [
      '#markup' => $action_data_entry['entity_label'],
    ];

    $action_form['entity_type_label'] = [
      '#markup' => $action_data_entry['entity_type_label'],
    ];

    $action_form['action'] = [
      '#type' => 'select',
      '#options' => [
        TRUE => t('Overwrite existing'),
        FALSE => t('Keep existing'),
      ],
    ];

    //If the entry has broken linkage with content
    if (isset($this->broken_entities[$action_data_entry['entry_uuid']])) {
      $warning_markup = [];
      $action_form['#attributes'] = ['class' => 'color-warning'];
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
        '#url' => $broken_entity['in_use_url'],
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

      $action_form['warning'] = $warning_markup;
    }
    return $action_form;
  }

  /**
   * Build a form for entries that doesn't need action
   *
   * @param $action_data_entry
   *
   * @return array
   */
  private function buildNonActionForm($action_data_entry) {
    $form = [];
    $form['entity_label'] = [
      '#markup' => $action_data_entry['entity_label'],
    ];

    $form['entity_type_label'] = [
      '#markup' => $action_data_entry['entity_type_label'],
    ];

    return $form;
  }

  /**
   * Build the accordion container for each type of import status
   *
   * @param array $entry_form - the entries
   * @param $name - the name of the section
   * @param $message - the message for the section
   * @param bool $open - is the section open by default
   *
   * @return array
   */
  private function buildAccordionActionForm(array $entry_form, $name, $message, $open = FALSE) {
    $form = [
      '#type' => 'details',
      '#open' => $open,
      '#title' => [
        '#markup' => $name,
      ],
    ];

    $form['information'] = [
      '#markup' => '<p>' . $message . '</p>',
    ];

    $form['entries']['indexes'] = [
      '#type' => 'table',
      '#header' => [
        ['data' => $this->t('Entity')],
        ['data' => $this->t('Entity type')],
      ],
    ];

    $form['entries']['indexes'] += $entry_form;
    return $form;
  }

  /**
   * The legacy form build.
   *
   * @param $form_state
   *
   * @return array
   * @throws \Drupal\Core\TempStore\TempStoreException
   */
  private function buildLegacyForm($form_state) {

    // Set a unique identifier for this form
    $this->store_key = $this->store_key ?: 'sync_validation_' . $this->uuidGenerator->generate();

    $form['store_key'] = [
      '#type' => 'hidden',
      '#value' => $this->store_key,
    ];

    // Get the data saved during validation and delete straight after from the
    // store.
    $sync_data = $this->sitestudioTempSharedStore->get($this->store_key);
    if($sync_data !== NULL) {
      if(isset($sync_data['action_data'])) {
        $this->action_data = $sync_data['action_data'];
      }
      if(isset($sync_data['broken_entities'])) {
        $this->broken_entities = $sync_data['broken_entities'];
      }
      $this->sitestudioTempSharedStore->delete($this->store_key);
    }

    // If there was an error in the batch validating the package
    // Revert back to step 0
    if($sync_data === FALSE) {
      $this->step = 0;
      $this->action_data = [];
      $this->broken_entities = [];
    }

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

    if ($this->step == 1) {

      $action_form = [];
      $new_form = [];
      $locked_form = [];
      $no_change_form = [];
      foreach ($this->action_data as $uuid => $action_data_entry) {

        // Render a form for each type of entry that the user might want to take
        // actions against
        switch ($action_data_entry['entry_action_state']) {
          case ENTRY_EXISTING_ASK:
            // If the entry requires user input.
            $action_form[$uuid] = $this->buildActionForm($action_data_entry);
            break;

          case ENTRY_NEW_IMPORTED:
            $new_form[$uuid] = $this->buildNonActionForm($action_data_entry);
            break;

          case ENTRY_EXISTING_LOCKED:
            $locked_form[$uuid] = $this->buildNonActionForm($action_data_entry);
            break;

          case ENTRY_EXISTING_NO_CHANGES:
            $no_change_form[$uuid] = $this->buildNonActionForm($action_data_entry);
            break;
        }
      }

      // Actions are required before importing
      if(!empty($action_form)) {
        $message = $this->t('There are differences between your local entities and the entities you are trying to import. Action is required.');
        $form['entry_actions'] = $this->buildAccordionActionForm($action_form, $this->t('Action needed. Difference between local entities and package.'), $message, TRUE);
        $form['entry_actions']['entries']['indexes']['#header'][] = ['data' => $this->t('Action')];

        if (!empty($this->broken_entities)) {
          $form['entry_actions']['entries']['indexes']['#header'][] = ['data' => $this->t('Warning')];
          $this->messenger()->addWarning($this->t('Some entities you are importing are missing populated fields, this will result in a loss of content. Please check the warnings listed below.'));
        }
      }

      if(!empty($new_form)) {
        $message = $this->t("These entities don't currently exist on your website. They will be imported from the package. No action is required");
        $form['entry_new'] = $this->buildAccordionActionForm($new_form, $this->t('New entities'), $message);
      }

      if(!empty($locked_form)) {
        $message = $this->t('These entities are locked on your website. They will not be imported from the package. If you want these entities to import from the package you should first unlock them. No action is required');
        $form['entry_locked'] = $this->buildAccordionActionForm($locked_form, $this->t('Locked entities'), $message);
      }

      if(!empty($no_change_form)) {
        $message = $this->t('These entities are the same on your website and in the package. They will not be imported from the package. No action is required');
        $form['entry_no_change'] = $this->buildAccordionActionForm($no_change_form, $this->t('Identical entities'), $message);
      }
    }

    // Standard actions for all steps.
    $form['actions'] = [
      '#type' => 'actions',
      'submit' => [
        '#type' => 'submit',
        '#value' => $this->step == 0 ? $this->t('Validate package') : $this->t('Import package'),
        '#button_type' => 'primary',
        // Don't disable the button if validation has completed otherwise the
        // form won't submit.
        '#disabled' => !$form_state->isValidationComplete(),
        '#name' => 'legacy_import',
      ],
    ];

    return $form;
  }

  /**
   * The legacy single file validate method
   *
   * @param $form_state
   *
   * @return false
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundExceptionT
   */
  private function validateLegacyForm($form_state) {
    if ($form_state->getTriggeringElement()['#name'] == 'package_yaml_upload_button') {
      // Get the uploaded file entity.
      $file_id = $form_state->getUserInput()['files']['package_yaml'];
      if ($file_entity = $this->entityTypeManager->getStorage('file')
        ->load(trim($file_id))) {
        $this->file_uri = $file_entity->getFileUri();
      }else {
        $form_state->setErrorByName('package_yaml', $this->t("The uploaded file can't be retrieved please try again"));
        return FALSE;
      }
    }
  }

  /**
   * The legacy single file submit method
   *
   * @param $form_state
   *
   * @return bool
   */
  private function legacyFormSubmit($form_state) {

    if($this->step == 0 && empty($this->action_data) && $this->file_uri) {
      $operations = $this->packagerManager->validatePackageBatch($this->file_uri, $this->store_key);

      $batch = [
        'title' => t('Validating package.'),
        'finished' => '\Drupal\cohesion_sync\Controller\BatchImportController::batchFinishedValidationCallback',
        'operations' => $operations,
      ];

      batch_set($batch);

      $this->step = 1;
      $form_state->setRebuild();
      return TRUE;
    }

    // Remove action entries depending on user input.
    if ($this->step == 1) {
      if ($form_state->getValue('indexes')) {
        foreach ($form_state->getValue('indexes') as $uuid => $item) {
          if ($item['action'] == FALSE) {
            $this->action_data[$uuid]['entry_action_state'] = ENTRY_EXISTING_IGNORED;
          }
          else {
            $this->action_data[$uuid]['entry_action_state'] = ENTRY_EXISTING_OVERWRITTEN;
          }
        }
      }
    }

    // Check to see if there is anything to do.
    if (!count($this->action_data)) {
      \Drupal::messenger()->addMessage($this->t('There were no changes detected. Nothing was applied.'));
      return TRUE;
    }

    // Apply all the items from the import.
    // Clear the sync report.
    \Drupal::service('tempstore.private')
      ->get('sync_report')
      ->delete('report');

    // Get the batch operations for the sync import.
    $operations = $this->packagerManager->applyBatchYamlPackageStream($this->file_uri, $this->action_data);

    $batch = [
      'title' => t('Importing configuration.'),
      'finished' => '\Drupal\cohesion_sync\Controller\BatchImportController::batchFinishedCallback',
      'operations' => $operations,
    ];

    // Set the sync report.
    \Drupal::service('tempstore.private')->get('sync_report')->set('report', $this->action_data);

    batch_set($batch);
    return TRUE;
  }

}
