<?php

namespace Drupal\cohesion_sync\Drush\Commands;

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
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigException;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageComparerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Utility\Error;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Drush\Exceptions\UserAbortException;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Cohesion Package Import Command.
 */
class CohesionSyncImportCommand extends DrushCommands {

  const LEGACY_FORMAT_PATTERN = "%(.*?)\.(yml_|yml)$%";

  /**
   * CohesionSyncImportCommand constructor.
   *
   * @param \Drupal\Core\Config\StorageInterface $configStorage
   * @param \Drupal\cohesion\Services\RebuildInuseBatch $rebuildInuseBatch
   * @param \Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher
   * @param \Drupal\cohesion_sync\Services\SyncImport $syncImport;
   * @param \Drupal\cohesion\UsageUpdateManager $usageUpdateManager
   * @param \Drupal\Core\Config\ConfigManagerInterface $configManager
   * @param \Drupal\Core\Cache\CacheBackendInterface $configCache
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\Lock\LockBackendInterface $lock
   * @param \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManager
   * @param \Drupal\Core\Extension\ModuleInstallerInterface $moduleInstaller
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   * @param \Drupal\Core\Extension\ModuleExtensionList $moduleExtensionList
   * @param \Drupal\Core\Extension\ThemeExtensionList $themeExtensionList
   */
  public function __construct(
    protected StorageInterface $configStorage,
    protected RebuildInuseBatch $rebuildInuseBatch,
    protected EventDispatcherInterface $dispatcher,
    protected SyncImport $syncImport,
    protected UsageUpdateManager $usageUpdateManager,
    protected ConfigManagerInterface $configManager,
    protected CacheBackendInterface $configCache,
    protected ModuleHandlerInterface $moduleHandler,
    protected LockBackendInterface $lock,
    protected TypedConfigManagerInterface $typedConfigManager,
    protected ModuleInstallerInterface $moduleInstaller,
    protected ThemeHandlerInterface $themeHandler,
    protected TranslationInterface $stringTranslation,
    protected ModuleExtensionList $moduleExtensionList,
    protected ThemeExtensionList $themeExtensionList,
  ) {
    parent::__construct();
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('config.storage'),
      $container->get('cohesion.rebuild_inuse_batch'),
      $container->get('event_dispatcher'),
      $container->get('cohesion_sync.sync_import'),
      $container->get('cohesion_usage.update_manager'),
      $container->get('config.manager'),
      $container->get('cache.config'),
      $container->get('module_handler'),
      $container->get('lock'),
      $container->get('config.typed'),
      $container->get('module_installer'),
      $container->get('theme_handler'),
      $container->get('string_translation'),
      $container->get('extension.list.module'),
      $container->get('extension.list.theme'),
    );
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
  public function siteStudioImport(
    array $options = [
      'path' => NULL,
      'diff' => NULL,
      'extra-validation' => FALSE,
    ],
  ) {
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
    $recreates = [];
    if ($storage_comparer->createChangelist()->hasChanges()) {
      $recreates = $storage_comparer->getRecreates();
      $change_list = $this->syncImport->buildChangeList($storage_comparer->getChangelist());
      if (isset($options['diff']) && $options['diff'] == TRUE) {
        $output = $this->getDiff($source_storage);
        $this->output()->writeln($output);
        if (!$this->io()
          ->confirm(dt('Import the listed configuration changes?'))) {
          throw new UserAbortException();
        }
      }
      drush_op([$this, 'doImport'], $storage_comparer);
    }
    else {
      $this->yell('There are no new or updated config entities to import.');
    }

    return $this->handleRebuilds($storage_comparer, $change_list, $recreates);
  }

  /**
   * Handles rebuild during package import.
   *
   * @param \Drupal\Core\Config\StorageComparerInterface $storage_comparer
   *   Storage Comparer service.
   * @param array $change_list
   *   List of changes.
   * @param array $recreates
   *  List of entities recreated during this import.
   *
   * @return \Consolidation\AnnotatedCommand\CommandResult
   *   Returns success regardless if the rebuild is required or not.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function handleRebuilds(StorageComparerInterface $storage_comparer, array $change_list, array $recreates) {
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

    if(!empty($recreates)) {
      $batch = [
        'title' => t('Rebuilding in use entities'),
        'operations' => [
          [
            [BatchImportController::class, 'handleInuse'],
            [$recreates],
          ],
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

  /**
   * @param \Drupal\Core\Config\StorageComparerInterface $storage_comparer
   *
   * @return void
   * @throws \Exception
   */
  public function doImport(StorageComparerInterface $storage_comparer): void {
    $config_importer = new ConfigImporter(
      $storage_comparer,
      $this->dispatcher,
      $this->configManager,
      $this->lock,
      $this->typedConfigManager,
      $this->moduleHandler,
      $this->moduleInstaller,
      $this->themeHandler,
      $this->stringTranslation,
      $this->moduleExtensionList,
      $this->themeExtensionList
    );
    if ($config_importer->alreadyImporting()) {
      $this->logger()->warning('Another request may be synchronizing configuration already.');
    } else {
      try {
        // This is the contents of \Drupal\Core\Config\ConfigImporter::import.
        // Copied here so we can log progress.
        if ($config_importer->hasUnprocessedConfigurationChanges()) {
          $sync_steps = $config_importer->initialize();
          foreach ($sync_steps as $step) {
            $context = [];
            do {
              $config_importer->doSyncStep($step, $context);
              if (isset($context['message'])) {
                $this->logger()->notice(str_replace('Synchronizing', 'Synchronized', (string) $context['message']));
              }

              // Installing and uninstalling modules might trigger
              // batch operations. Let's process them here.
              // @see \Drush\Commands\pm\PmCommands::install()
              if ($step === 'processExtensions' && batch_get()) {
                drush_backend_batch_process();
              }
            } while ($context['finished'] < 1);
          }
          // Clear the cache of the active config storage.
          $this->configCache->deleteAll();
        }
        if ($config_importer->getErrors()) {
          throw new ConfigException('Errors occurred during import');
        } else {
          $this->logger()->success('The configuration was imported successfully.');
        }
      } catch (ConfigException $e) {
        // Return a negative result for UI purposes. We do not differentiate
        // between an actual synchronization error and a failed lock, because
        // concurrent synchronizations are an edge-case happening only when
        // multiple developers or site builders attempt to do it without
        // coordinating.
        $message = 'The import failed due to the following reasons:' . "\n";
        $message .= implode("\n", $config_importer->getErrors());

        $variables = Error::decodeException($e);
        $this->logger()->log(LogLevel::ERROR, $message, $variables);
        throw new \Exception($message, $e->getCode(), $e);
      }
    }
  }

  /**
   * Fetches diff output based on Drush version.
   *
   * @param \Drupal\Core\Config\StorageInterface $source_storage
   * @return array|bool|string
   * @throws \Exception
   */
  protected function getDiff(StorageInterface $source_storage): array|bool|string {
    if (Drush::getMajorVersion() == '11' && class_exists('\Drush\Drupal\Commands\config\ConfigCommands')) {
      // phpcs:ignore
      $output = \Drush\Drupal\Commands\config\ConfigCommands::getDiff($this->configStorage, $source_storage, $this->output());
    }
    elseif (Drush::getMajorVersion() == '12' && class_exists('\Drush\Commands\config\ConfigCommands')) {
      // phpcs:ignore
      $output = \Drush\Commands\config\ConfigCommands::getDiff($this->configStorage, $source_storage, $this->output());
    }
    else {
      throw new \Exception("Unsupported Drush version detected.");
    }

    return $output;
  }

}
