<?php

namespace Drupal\cohesion_sync\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
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

  const CONFIG_PREFIX = 'cohesion_';

  const COMPLETE_REBUILD = [
    'base_unit_settings' => 'cohesion_website_settings.cohesion_website_settings.base_unit_settings',
    'responsive_grid_settings' => 'cohesion_website_settings.cohesion_website_settings.responsive_grid_settings',
  ];

  const ENTITY_WITH_DEPENDENCY = [
    'cohesion_scss_variable' => 'cohesion_website_settings.cohesion_scss_variable.',
    'cohesion_style_guide' => 'cohesion_style_guide.cohesion_style_guide.',
    'cohesion_color' => 'cohesion_website_settings.cohesion_color.',
    'cohesion_font_stack' => 'cohesion_website_settings.cohesion_font_stack.',
  ];

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
   * UsageUpdateManager service.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * Event Dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $dispatcher;

  /**
   * CohesionSyncImportCommand constructor.
   *
   * @param \Drush\Drupal\Commands\config\ConfigImportCommands $configImportCommands
   *   Drush config import commands service.
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   *   Config storage service.
   */
  public function __construct(
    ConfigImportCommands $configImportCommands,
    StorageInterface $configStorage,
    RebuildInuseBatch $rebuildInuseBatch,
    UsageUpdateManager $usageUpdateManager,
    EventDispatcherInterface $dispatcher
  ) {
    parent::__construct();
    $this->configImportCommands = $configImportCommands;
    $this->configStorage = $configStorage;
    $this->rebuildInuseBatch = $rebuildInuseBatch;
    $this->usageUpdateManager = $usageUpdateManager;
    $this->dispatcher = $dispatcher;
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
      throw new \Exception('No destination directory provided and no value set in `site_studio_sync` settings.');
    }

    $files = file_get_contents($path . '/' . CohesionFileStorage::FILE_INDEX_FILENAME);
    if ($files) {
      $files = json_decode($files, TRUE);
    }
    $this->handleFileSync($files, $path);

    // Determine source directory.
    $source_storage = new CohesionFileStorage($path);
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
      $change_list = $this->buildChangeList($storage_comparer->getChangelist());
      if ($options['diff']) {
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
   * Checks if imported config requires full rebuild.
   *
   * @param array $change_list
   *   List of imported config names.
   *
   * @return bool
   *   True if full rebuild required.
   */
  protected function needsCompleteRebuild(array $change_list): bool {
    foreach ($change_list as $name) {
      if (in_array($name, self::COMPLETE_REBUILD)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Builds single-dimensional array of changes out of multi-dimensional array.
   *
   * @param array $changes
   *   Multi-dimensional array of changes, outer keys are CRUD Operations.
   *
   * @return array
   *   Flattened array of changed config names.
   */
  protected function buildChangeList(array $changes): array {
    $change_list = [];
    foreach ($changes['create'] as $name) {
      if ($name) {
        $change_list[] = $name;
      }
    }
    foreach ($changes['update'] as $name) {
      if ($name) {
        $change_list[] = $name;
      }
    }

    return $change_list;
  }

  /**
   * Finds entities affected by config import and returns an array.
   *
   * @param array $change_list
   *   Array of imported config names.
   * @param \Drupal\Core\Config\StorageComparerInterface $storageComparer
   *   Storage comparer service.
   *
   * @return array
   *   List of entities that need rebuild.
   */
  protected function findAffectedEntities(array $change_list, StorageComparerInterface $storageComparer): array {
    $rebuild_list = [];

    foreach ($change_list as $name) {
      if (str_starts_with($name, self::CONFIG_PREFIX)) {
        $uuid = $storageComparer->getSourceStorage()->read($name)['uuid'];
        $type = explode('.', $name)[1];
        $rebuild_list[$uuid] = $type;

        foreach (self::ENTITY_WITH_DEPENDENCY as $entity_type) {
          $target_entity = $storageComparer->getTargetStorage()->read($name);
          if ($target_entity !== FALSE && str_starts_with($name, $entity_type)) {
            $dependant_entities = $this->usageUpdateManager->getInUseEntitiesListByUuid($target_entity['uuid']);
            if (!empty($dependant_entities)) {
              $rebuild_list = array_merge($rebuild_list, $dependant_entities);
            }
          }
        }
      }
    }

    return $rebuild_list;
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

    if ($this->needsCompleteRebuild($change_list)) {
      $this->yell('Doing complete rebuild.');
      $batch = WebsiteSettingsController::batch(TRUE);
      batch_set($batch);
    }
    else {
      $rebuild_list = $this->findAffectedEntities($change_list, $storage_comparer);
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
    $this->dispatcher->dispatch($file_sync_event::NAME, $file_sync_event);

    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');
    if ($cohesion_file_sync_messages['new_files'] || $cohesion_file_sync_messages['updated_files']) {
      $this->yell(sprintf('Imported %s new and updated %s existing non-config files.', $cohesion_file_sync_messages['new_files'], $cohesion_file_sync_messages['updated_files']));
    }
    else {
      $this->yell('No non-config files were imported or updated during sync.');
    }
  }

}
