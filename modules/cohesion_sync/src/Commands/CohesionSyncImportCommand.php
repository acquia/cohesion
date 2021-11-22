<?php

namespace Drupal\cohesion_sync\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
use Drupal\cohesion_sync\Services\SyncImport;
use Drupal\cohesion_website_settings\Controller\WebsiteSettingsController;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\StorageComparer;
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
   */
  public function __construct(
    ConfigImportCommands $configImportCommands,
    StorageInterface $configStorage,
    RebuildInuseBatch $rebuildInuseBatch,
    EventDispatcherInterface $dispatcher,
    SyncImport $sync_import
  ) {
    parent::__construct();
    $this->configImportCommands = $configImportCommands;
    $this->configStorage = $configStorage;
    $this->rebuildInuseBatch = $rebuildInuseBatch;
    $this->dispatcher = $dispatcher;
    $this->syncImport = $sync_import;
  }

  /**
   * Import Cohesion packages from sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli.
   *
   * @option path
   *   Specify a local or remote path to a *.package.yml file.
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

    // Determine source directory.
    $source_storage = new CohesionFileStorage($path);
    // Handle the files.
    $files = $source_storage->getFilesJson();
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
      $replacement_storage->replaceData($name, $data);
    }

    $source_storage = $replacement_storage;
    $storage_comparer = new StorageComparer($source_storage, $active_storage);

    $change_list = [];
    if ($storage_comparer->createChangelist()->hasChanges()) {
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

    return $this->handleRebuilds($storage_comparer, $change_list);
  }

  /**
   * Handles rebuild during package import.
   *
   * @param \Drupal\Core\Config\StorageComparerInterface $storage_comparer
   *   Storage Comparer service.
   * @param array $change_list
   *   List of changes.
   *
   * @return \Consolidation\AnnotatedCommand\CommandResult
   *   Returns success regardless if the rebuild is required or not.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function handleRebuilds(StorageComparerInterface $storage_comparer, array $change_list) {
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
   *   Path of the the package that is being imported.
   */
  protected function handleFileSync(array $files, string $path) {
    $file_sync_event = new SiteStudioSyncFilesEvent($files, $path);
    $this->dispatcher->dispatch($file_sync_event::IMPORT, $file_sync_event);

    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');
    if ($cohesion_file_sync_messages['new_files'] || $cohesion_file_sync_messages['updated_files']) {
      $this->yell(sprintf('Imported %s new and updated %s existing non-config files.', $cohesion_file_sync_messages['new_files'], $cohesion_file_sync_messages['updated_files']));
    }
    else {
      $this->yell('No non-config files were imported or updated during sync.');
    }
  }

}
