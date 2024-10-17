<?php

namespace Drupal\cohesion_sync\Form;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\Config\CohesionStorageComparer;
use Drupal\cohesion_sync\Controller\BatchImportController;
use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
use Drupal\cohesion_sync\Services\SyncImport;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\ConfigImporterException;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\Importer\ConfigImporterBatch;
use Drupal\Core\Config\ImportStorageTransformer;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Construct the storage changes in a configuration synchronization form.
 *
 * @internal
 */
class CohesionConfigSync extends FormBase {

  /**
   * The database lock object.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * The sync configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $fileStorage;

  /**
   * The active configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $activeStorage;

  /**
   * The snapshot configuration object.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $snapshotStorage;

  /**
   * Event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * The typed config manager.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The theme handler.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The module installer.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The module extension list.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * The sync import service.
   *
   * @var \Drupal\cohesion_sync\Services\SyncImport
   */
  protected $syncImport;

  /**
   * The usage update manager service
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * The import transformer service.
   *
   * @var \Drupal\Core\Config\ImportStorageTransformer
   */
  protected $importTransformer;

  /**
   * The theme extension list.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected $themeExtensionList;

  /**
   * Constructs the object.
   *
   * @param \Drupal\Core\Config\StorageInterface $file_storage
   *   The source storage.
   * @param \Drupal\Core\Config\StorageInterface $active_storage
   *   The target storage.
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   *   The lock object.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher
   *   Event dispatcher.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   Configuration manager.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typed_configmanager
   *   The typed config manager.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $module_installer
   *   The module installer.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   * @param \Drupal\Core\Extension\ModuleExtensionList $extension_list_module
   *   The module extension list
   * @param \Drupal\cohesion_sync\Services\SyncImport $sync_import
   *   The sync import service.
   * @param \Drupal\cohesion\UsageUpdateManager $usageUpdateManager
   *   The site studio usage update manager
   * @param \Drupal\Core\Extension\ThemeExtensionList $themeExtensionList
   *    The theme extension list.
   * @param ?\Drupal\Core\Config\ImportStorageTransformer $import_transformer
   *    The import transformer service.
   */
  public function __construct(
    StorageInterface $file_storage,
    StorageInterface $active_storage,
    LockBackendInterface $lock,
    EventDispatcherInterface $event_dispatcher,
    ConfigManagerInterface $config_manager,
    TypedConfigManagerInterface $typed_configmanager,
    ModuleHandlerInterface $module_handler,
    ModuleInstallerInterface $module_installer,
    ThemeHandlerInterface $theme_handler,
    RendererInterface $renderer,
    ModuleExtensionList $extension_list_module,
    SyncImport $sync_import,
    UsageUpdateManager $usageUpdateManager,
    ThemeExtensionList $themeExtensionList,
    ?ImportStorageTransformer $import_transformer = NULL,
  ) {
    $this->fileStorage = $file_storage;
    $this->activeStorage = $active_storage;
    $this->lock = $lock;
    $this->eventDispatcher = $event_dispatcher;
    $this->configManager = $config_manager;
    $this->typedConfigManager = $typed_configmanager;
    $this->moduleHandler = $module_handler;
    $this->moduleInstaller = $module_installer;
    $this->themeHandler = $theme_handler;
    $this->renderer = $renderer;
    $this->moduleExtensionList = $extension_list_module;
    $this->syncImport = $sync_import;
    $this->usageUpdateManager = $usageUpdateManager;
    $this->themeExtensionList = $themeExtensionList;
    $this->importTransformer = $import_transformer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('cohesion_sync.import_file_storage'),
      $container->get('config.storage'),
      $container->get('lock.persistent'),
      $container->get('event_dispatcher'),
      $container->get('config.manager'),
      $container->get('config.typed'),
      $container->get('module_handler'),
      $container->get('module_installer'),
      $container->get('theme_handler'),
      $container->get('renderer'),
      $container->get('extension.list.module'),
      $container->get('cohesion_sync.sync_import'),
      $container->get('cohesion_usage.update_manager'),
      $container->get('extension.list.theme'),
      $container->get('config.import_transformer'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'site_studio_config_sync';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $replacement_storage = new StorageReplaceDataWrapper($this->activeStorage);
    foreach ($this->fileStorage->listAll() as $name) {
      $data = $this->fileStorage->read($name);
      $replacement_storage->replaceData($name, $data);
    }

    $files = $this->fileStorage->getFiles();
    $storage_comparer = new CohesionStorageComparer($replacement_storage, $this->activeStorage, $this->usageUpdateManager);
    $path = _cohesion_sync_temp_directory();

    $file_sync_event = new SiteStudioSyncFilesEvent($files, $path);
    $this->eventDispatcher->dispatch($file_sync_event, $file_sync_event::CHANGES);
    // Store the path for use in the "submitForm".
    $form_state->set('sync_dir', $path);

    $cohesion_file_changes = &drupal_static('cohesion_file_sync_messages');

    if ((empty($replacement_storage->listAll()) || !$storage_comparer->createChangelist()->hasChangesWithLocked()) && (empty($cohesion_file_changes['new_files']) && empty($cohesion_file_changes['updated_files']))) {
      $form['no_changes'] = [
        '#type' => 'table',
        '#header' => [$this->t('Name'), $this->t('Operations')],
        '#rows' => [],
        '#empty' => $this->t('There are no configuration changes to import.'),
      ];
      $form['actions']['#access'] = FALSE;
      return $form;
    }

    // Store the comparer for use in the form submit.
    $form_state->set('storage_comparer', $storage_comparer);

    $collection = StorageInterface::DEFAULT_COLLECTION;

    $create_count = count($cohesion_file_changes['new_files']);
    $update_count = count($cohesion_file_changes['updated_files']);

    $recreates = [];
    foreach (array_intersect($storage_comparer->getSourceStorage()->listAll(), $storage_comparer->getTargetStorage()->listAll()) as $name) {
      $source_data = $storage_comparer->getSourceStorage()->read($name);
      $target_data = $storage_comparer->getTargetStorage()->read($name);
      if ($source_data !== $target_data) {
        if (isset($source_data['uuid']) && $source_data['uuid'] !== $target_data['uuid']) {
          // The entity has the same file as an existing entity but the UUIDs do
          // not match. This means that the entity has been recreated so config
          // synchronization should do the same.
          $recreates[] = $name;
        }
      }
    }

    foreach ($storage_comparer->getChangelist(NULL, $collection) as $config_change_type => $config_names) {

      $count_change_type = count($config_names);
      switch ($config_change_type) {
        case 'create':
          $create_count = $create_count + count($config_names);
          $count_change_type = $create_count;
          break;

        case 'update':
          $update_count = $update_count + count($config_names);
          $count_change_type = $update_count;
          break;
      }

      if ($count_change_type <= 0) {
        continue;
      }

      $form[$collection][$config_change_type]['heading'] = [
        '#type' => 'html_tag',
        '#tag' => 'h3',
      ];
      switch ($config_change_type) {
        case 'create':
          $form[$collection][$config_change_type]['heading']['#value'] = $this->formatPlural($create_count, '@count new', '@count new');
          break;

        case 'update':
          $form[$collection][$config_change_type]['heading']['#value'] = $this->formatPlural($update_count, '@count changed', '@count changed');
          break;

        case 'rename':
          $form[$collection][$config_change_type]['heading']['#value'] = $this->formatPlural(count($config_names), '@count renamed', '@count renamed');
          break;

        case 'locked':
          $form[$collection][$config_change_type]['heading']['#value'] = $this->formatPlural(count($config_names), '@count locked (will not be updated)', '@count locked');
          break;

        case 'delete':
          $form[$collection][$config_change_type]['heading']['#value'] = $this->formatPlural(count($config_names), '@count removed', '@count removed');
          break;
      }
      $form[$collection][$config_change_type]['list'] = [
        '#type' => 'table',
        '#header' => [
          ['data' => $this->t('Name')],
          ['data' => $this->t('Status')],
        ],
      ];

      foreach ($config_names as $config_name) {
        if ($config_change_type == 'rename') {
          $names = $storage_comparer->extractRenameNames($config_name);
          $config_name = $this->t('@source_name to @target_name', [
            '@source_name' => $names['old_name'],
            '@target_name' => $names['new_name'],
          ]);
        }

        if (in_array($config_name, $recreates)) {
          $form[$collection][$config_change_type]['list']['#rows'][] = [
            'name' => $config_name,
            'status' => 'recreated due to UUID differences',
          ];
          continue;
        }

        $form[$collection][$config_change_type]['list']['#rows'][] = [
          'name' => $config_name,
        ];
      }
    }

    foreach ($cohesion_file_changes['new_files'] as $new_files) {
      $form[$collection]['create']['list']['#rows'][] = [
        'name' => $new_files,
      ];
    }

    foreach ($cohesion_file_changes['updated_files'] as $new_files) {
      $form[$collection]['update']['list']['#rows'][] = [
        'name' => $new_files,
      ];
    }

    // The package can to be imported in it contains new or updated file and/or
    // if it contains config changes except locked entity. If it only contains
    // locked entities there is nothing to be imported.
    if($storage_comparer->hasChanges() || count($cohesion_file_changes['new_files']) > 0 || count($cohesion_file_changes['updated_files']) > 0) {

      $form['actions'] = [];

      $form['actions']['import_container'] = [
        '#type' => 'fieldset',
        '#description' => $this->t('Import all configuration and files with default Drupal validation only.'),
        '#weight' => 200,
      ];

      $form['actions']['import_container']['import'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#name' => 'import',
        '#value' => $this->t('Import all'),
      ];

      $form['actions']['import_validation_container'] = [
        '#type' => 'fieldset',
        '#description' => $this->t('Import all configuration and files with default Drupal validation and perform data integrity validation on Site Studio configuration. This import takes more time and resources.'),
        '#weight' => 200,
      ];

      $form['actions']['import_validation_container']['import_validation'] = [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#name' => 'import_validation',
        '#value' => $this->t('Import all with extra validation'),
      ];
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $config_importer = new ConfigImporter(
      $form_state->get('storage_comparer'),
      $this->eventDispatcher,
      $this->configManager,
      $this->lock,
      $this->typedConfigManager,
      $this->moduleHandler,
      $this->moduleInstaller,
      $this->themeHandler,
      $this->getStringTranslation(),
      $this->moduleExtensionList,
      $this->themeExtensionList,
    );
    if ($config_importer->alreadyImporting()) {
      $this->messenger()->addStatus($this->t('Another request may be synchronizing configuration already.'));
    }
    else {
      try {

        if ($form_state->getTriggeringElement()['#name'] == 'import_validation') {
          $cohesion_sync_import_options = &drupal_static('cohesion_sync_import_options');
          $cohesion_sync_import_options['extra-validation'] = TRUE;
        }

        $recreates = $form_state->get('storage_comparer')->getRecreates();

        $sync_steps = $config_importer->initialize();
        $batch = [
          'operations' => [],
          'finished' => [BatchImportController::class, 'finish'],
          'title' => t('Synchronizing configuration'),
          'init_message' => t('Starting configuration synchronization.'),
          'progress_message' => t('Completed step @current of @total.'),
          'error_message' => t('Configuration synchronization has encountered an error.'),
        ];
        $batch['operations'][] = [
          [BatchImportController::class, 'fileImport'],
          [$this->fileStorage, $form_state->get('sync_dir')],
        ];

        foreach ($sync_steps as $sync_step) {
          $batch['operations'][] = [
            [ConfigImporterBatch::class, 'process'],
            [$config_importer, $sync_step],
          ];
        }

        $batch['operations'][] = [
          [BatchImportController::class, 'handleRebuilds'],
          [$form_state->get('storage_comparer')],
        ];

        if (!empty($recreates)) {
          $batch['operations'][] = [
            [BatchImportController::class, 'handleInuse'],
            [$recreates],
          ];
        }
        batch_set($batch);

        $form_state->setRedirect('cohesion_sync.import');
      }
      catch (ConfigImporterException $e) {
        // There are validation errors.
        $this->messenger()->addError($this->t('The configuration cannot be imported because it failed validation for the following reasons:'));
        foreach ($config_importer->getErrors() as $message) {
          $this->messenger()->addError($message);
          $this->logger('cohesion_sync')->error($message);
        }
      }
    }
  }

}
