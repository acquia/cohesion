<?php

namespace Drupal\cohesion_sync\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Config\CohesionStorageComparer;
use Drupal\cohesion_sync\Controller\BatchImportController;
use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
use Drupal\cohesion_sync\Exception\LegacyPackageFormatException;
use Drupal\cohesion_sync\Exception\UnsupportedFileFormatException;
use Drupal\cohesion_sync\Services\SyncImport;
use Drupal\cohesion_website_settings\Controller\WebsiteSettingsController;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\StorageComparerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Site\Settings;
use Drush\Commands\DrushCommands;
use Drush\Drupal\Commands\config\ConfigCommands;
use Drush\Drupal\Commands\config\ConfigImportCommands;
use Drush\Exceptions\UserAbortException;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Cohesion Package Import Command.
 */
class CohesionSyncImportCommand extends DrushCommands {

  const LEGACY_FORMAT_PATTERN = "%(.*?)\.(yml_|yml)$%";

  /**
   * ConfigStorage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Drush ConfigImportsCommands service.
   *
   * @var \Drush\Drupal\Commands\config\ConfigImportCommands
   */
  protected $configImportCommands;

  /**
   * RebuildInUseBatch.
   *
   * @var \Drupal\cohesion\Services\RebuildInuseBatch
   */
  protected $rebuildInuseBatch;

  /**
   * Event Dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

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
   * CohesionSyncImportCommand constructor.
   *
   * @param \Drush\Drupal\Commands\config\ConfigImportCommands $configImportCommands
   *   Drush config import commands service.
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage service.
   * @param \Drupal\cohesion\Services\RebuildInuseBatch $rebuildInuseBatch
   *   Rebuild in use service.
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   *   Event dispacher service.
   * @param \Drupal\cohesion_sync\Services\SyncImport $sync_import;
   *   The sync import service.
   * @param \Drupal\cohesion\UsageUpdateManager $usageUpdateManager
   *   The usage update manager
   */
  public function __construct(
    ConfigImportCommands $configImportCommands,
    StorageInterface $configStorage,
    RebuildInuseBatch $rebuildInuseBatch,
    EventDispatcherInterface $dispatcher,
    SyncImport $sync_import,
    UsageUpdateManager $usageUpdateManager
  ) {
    parent::__construct();
    $this->configImportCommands = $configImportCommands;
    $this->configStorage = $configStorage;
    $this->rebuildInuseBatch = $rebuildInuseBatch;
    $this->dispatcher = $dispatcher;
    $this->syncImport = $sync_import;
    $this->usageUpdateManager = $usageUpdateManager;
  }

  /**
   * Import Cohesion packages from sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli.
   *
   * @option path
   *   Specify path to a directory with package files.
   * @option diff
   *   Show preview as a diff.
   * @option extra-validation
   *   Do extra validation steps during sync. Default behaviour is FALSE.
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sitestudio:package:import
   * @aliases cohesion:package:import
   */
  public function siteStudioImport(array $options = [
    'path' => NULL,
    'diff' => NULL,
    'extra-validation' => FALSE,
  ]) {
    $cohesion_sync_import = &drupal_static('cohesion_sync_import');
    $cohesion_sync_import = TRUE;
    $cohesion_sync_import_options = &drupal_static('cohesion_sync_import_options');
    $cohesion_sync_import_options = $options;

    $path = $options['path'] ?: Settings::get('site_studio_sync');
    if ($path === NULL) {
      $path = COHESION_SYNC_DEFAULT_DIR;
    }
    elseif ($this->legacyFilename($path)) {
      $message = 'You are attempting to import legacy format package with the new format command. Legacy package format is not supported by this command. For more information refer to the documentation page: https://sitestudiodocs.acquia.com/6.9/user-guide/deprecating-legacy-package-system';
      return CommandResult::dataWithExitCode($message, self::EXIT_FAILURE);
    }

    // Determine source directory.
    $source_storage = new CohesionFileStorage($path);
    // Handle the files.
    $files = $source_storage->getFiles();
    try {
      $this->handleFileSync($files, $path);
    } catch (\Exception $e) {
      $this->yell($e->getMessage());
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }
    // Determine $source_storage in partial case.
    $active_storage = $this->configStorage;
    $replacement_storage = new StorageReplaceDataWrapper($active_storage);
    foreach ($source_storage->listAll() as $name) {
      $data = $source_storage->read($name);
      try {
        $this->validatePackageFile($data, $name . '.' . $source_storage::getFileExtension());
      }
      catch (\Exception $exception) {
        return CommandResult::dataWithExitCode($exception->getMessage(), self::EXIT_FAILURE);
      }
      $replacement_storage->replaceData($name, $data);
    }

    $source_storage = $replacement_storage;
    $storage_comparer = new CohesionStorageComparer($source_storage, $active_storage, $this->usageUpdateManager);

    $change_list = [];
    $in_use_list = [];
    if ($storage_comparer->createChangelist()->hasChanges()) {
      $in_use_list = $storage_comparer->getInuseDelete();
      $change_list = $this->syncImport->buildChangeList($storage_comparer->getChangelist());
      if (isset($options['diff']) && $options['diff'] == TRUE) {
        $output = ConfigCommands::getDiff($this->configStorage, $source_storage, $this->output());
        $this->output()->writeln($output);
        if (!$this->io()
          ->confirm(dt('Import the listed configuration changes?'))) {
          throw new UserAbortException();
        }
      }
      drush_op([$this->configImportCommands, 'doImport'], $storage_comparer);
    }
    else {
      $this->yell('There are no new or updated config entities to import.');
    }

    return $this->handleRebuilds($storage_comparer, $change_list, $in_use_list);
  }

  /**
   * Handles rebuild during package import.
   *
   * @param \Drupal\Core\Config\StorageComparerInterface $storage_comparer
   *   Storage Comparer service.
   * @param array $change_list
   *   List of changes.
   * @param array $in_use_list
   *  List of entities to rebuild the "in use" (coh_usage) table for
   *
   * @return \Consolidation\AnnotatedCommand\CommandResult
   *   Returns success regardless if the rebuild is required or not.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function handleRebuilds(StorageComparerInterface $storage_comparer, array $change_list, array $in_use_list) {
    if (empty($change_list)) {
      return CommandResult::exitCode(self::EXIT_SUCCESS);
    }

    if ($this->syncImport->needsCompleteRebuild($change_list)) {
      $this->yell('Doing complete rebuild.');
      $batch = WebsiteSettingsController::batch(TRUE);
      batch_set($batch);
    }
    else {
      $rebuild_list = $this->syncImport->findAffectedEntities($change_list, $storage_comparer);
      if (empty($rebuild_list)) {
        $this->yell('No rebuild required.');
        return CommandResult::exitCode(self::EXIT_SUCCESS);
      }
      $this->yell('Doing partial rebuild.');
      $this->rebuildInuseBatch->run($rebuild_list);
    }

    if(!empty($in_use_list)) {
      $batch = [
        'title' => t('Rebuilding in use entities'),
        'operations' => [
          [[BatchImportController::class, 'handleInuse'], [$in_use_list]],
        ],
        'init_message' => t('Starting in use rebuild.'),
        'progress_message' => t('Completed step @current of @total.'),
        'error_message' => t('Rebuild of "in use" entities has encountered an error.'),
      ];
      batch_set($batch);
    }

    $result = drush_backend_batch_process();
    $this->yell('Rebuild complete.');

    return is_array($result) && isset(array_shift($result)['error']) ? CommandResult::exitCode(self::EXIT_FAILURE) : CommandResult::exitCode(self::EXIT_SUCCESS);
  }

  /**
   * Handles file import during package import.
   *
   * @param array $files
   *   Array of files from the file index.
   * @param string $path
   *   Path of the package that is being imported.
   */
  protected function handleFileSync(array $files, string $path) {
    $file_sync_event = new SiteStudioSyncFilesEvent($files, $path);
    $this->dispatcher->dispatch($file_sync_event, $file_sync_event::IMPORT);

    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');
    if ($cohesion_file_sync_messages['new_files'] || $cohesion_file_sync_messages['updated_files']) {
      $this->yell(sprintf('Imported %s new and updated %s existing non-config files.', $cohesion_file_sync_messages['new_files'], $cohesion_file_sync_messages['updated_files']));
    }
    else {
      $this->yell('No non-config files were imported or updated during sync.');
    }
  }

  /**
   * Validates package data. New format yml files will always have uuid.
   *
   * @param array $data
   *
   * @return void
   * @throws \Exception
   */
  protected function validatePackageFile(array $data, string $filename) {
    if (array_key_exists('uuid', $data) === FALSE) {
      if (is_array(reset($data)) && array_key_exists('type', reset($data)) && array_key_exists('export', reset($data))) {
        throw new LegacyPackageFormatException($filename);
      }
      throw new UnsupportedFileFormatException($filename);
    }
  }

  /**
   * Validates filename to not contain ".yml_" or ".yml".
   *
   * @param string $filename
   *
   * @return bool
   */
  protected function legacyFilename(string $filename) {
    return (bool) preg_match(self::LEGACY_FORMAT_PATTERN, $filename);
  }

}
