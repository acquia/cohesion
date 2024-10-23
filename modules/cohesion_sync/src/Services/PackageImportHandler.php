<?php

namespace Drupal\cohesion_sync\Services;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Config\CohesionStorageComparer;
use Drupal\cohesion_sync\Controller\BatchImportController;
use Drupal\cohesion_sync\Exception\InvalidPackageDefinitionException;
use Drupal\cohesion_sync\Exception\PackageDefinitionMissingPropertiesException;
use Drupal\cohesion_sync\Exception\PackageListEmptyOrMissing;
use Drupal\cohesion_sync\PackageSourceManager;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Config\ConfigImporter;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\Importer\ConfigImporterBatch;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ModuleInstallerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Lock\LockBackendInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Site Studio package import handler.
 */
class PackageImportHandler {
  use StringTranslationTrait;

  const REQUIRED_PROPERTIES = ['type', 'source'];

  /**
   * EventDispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * PersistentDatabaseLock service.
   *
   * @var \Drupal\Core\Lock\LockBackendInterface
   */
  protected $lock;

  /**
   * TypedConfigManager service.
   *
   * @var \Drupal\Core\Config\TypedConfigManagerInterface
   */
  protected $typedConfigManager;

  /**
   * ModuleHandler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * ModuleInstaller service.
   *
   * @var \Drupal\Core\Extension\ModuleInstallerInterface
   */
  protected $moduleInstaller;

  /**
   * ThemeHandler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * TranslationManager service.
   *
   * @var \Drupal\Core\StringTranslation\TranslationManager
   */
  protected $stringTranslation;

  /**
   * ModuleExtensionList service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $moduleExtensionList;

  /**
   * ThemeExtensionList service.
   *
   * @var \Drupal\Core\Extension\ThemeExtensionList
   */
  protected $themeExtensionList;

  /**
   * Active Config Storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $activeStorage;

  /**
   * StorageReplacement service.
   *
   * @var \Drupal\config\StorageReplaceDataWrapper
   */
  protected $replacementStorage;

  /**
   * PackageSourceManager service.
   *
   * @var \Drupal\cohesion_sync\PackageSourceManager
   */
  protected $packageSourceManager;

  /**
   * UsageUpdateManager service.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * FileSystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * ConfigImporter service.
   *
   * @var \Drupal\Core\Config\ConfigImporter
   */
  protected $configImporter;

  /**
   * Site Studio Storage Comparer.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionStorageComparer
   */
  protected $storageComparer;

  /**
   * Packages.
   *
   * @var array
   */
  protected $packages = [];

  public function __construct(
    EventDispatcherInterface $eventDispatcher,
    FileSystemInterface $fileSystem,
    ConfigManagerInterface $configManager,
    LockBackendInterface $lock,
    TypedConfigManagerInterface $typedConfigManager,
    ModuleHandlerInterface $moduleHandler,
    ModuleInstallerInterface $moduleInstaller,
    ThemeHandlerInterface $themeHandler,
    TranslationManager $stringTranslation,
    ModuleExtensionList $moduleExtensionList,
    ThemeExtensionList $themeExtensionList,
    StorageInterface $activeStorage,
    UsageUpdateManager $usageUpdateManager,
    PackageSourceManager $packageSourceManager,
  ) {
    $this->eventDispatcher = $eventDispatcher;
    $this->fileSystem = $fileSystem;
    $this->configManager = $configManager;
    $this->lock = $lock;
    $this->typedConfigManager = $typedConfigManager;
    $this->moduleHandler = $moduleHandler;
    $this->moduleInstaller = $moduleInstaller;
    $this->themeHandler = $themeHandler;
    $this->stringTranslation = $stringTranslation;
    $this->moduleExtensionList = $moduleExtensionList;
    $this->themeExtensionList = $themeExtensionList;
    $this->activeStorage = $activeStorage;
    $this->usageUpdateManager = $usageUpdateManager;
    $this->packageSourceManager = $packageSourceManager;
    $this->replacementStorage = new StorageReplaceDataWrapper($this->activeStorage);
  }

  /**
   * Imports packages from list file.
   *
   * @param string $package_list_path
   *   Path to package list file.
   *
   * @return bool
   *   TRUE if batch was set successfully.
   *
   * @throws \Drupal\cohesion_sync\Exception\PackageListEmptyOrMissing
   * @throws \Exception
   */
  public function importPackagesFromPath(string $package_list_path): bool {
    $package_list = $this->readPackageList($package_list_path);

    return $this->importPackagesFromArray($package_list);
  }

  /**
   * Imports packages from array of paths.
   *
   * @param array $packages
   *   Array of packages.
   * @return bool
   *   TRUE if batch was set successfully.
   *
   * @throws \Exception
   */
  public function importPackagesFromArray(array $packages): bool {
    if (!empty($packages)) {
      $batch = $this->processPackageList($packages)->createImportBatch();
      if (!empty($batch)) {
        batch_set($batch);

        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Reads Package List file from provided path.
   *
   * @param string $package_list_path
   *   Path to package list file.
   *
   * @return array
   *   Package list.
   *
   * @throws \Drupal\cohesion_sync\Exception\PackageListEmptyOrMissing
   * @throws \Symfony\Component\Yaml\Exception\ParseException
   */
  protected function readPackageList(string $package_list_path): array {
    if (file_exists($package_list_path)) {
      $package_list = Yaml::parse(file_get_contents($package_list_path));
      if ($package_list !== NULL) {
        return $package_list;
      }
    }

    throw new PackageListEmptyOrMissing($package_list_path);
  }

  /**
   * @param array $package_list
   *
   * @return $this
   */
  protected function processPackageList(array $package_list) {
    foreach ($package_list as $package_entry) {
      if (is_array($package_entry)) {
        $this->validatePackageEntry($package_entry);
      }
      else {
        throw new InvalidPackageDefinitionException(gettype($package_entry));
      }
      $sourceService = $this->packageSourceManager->getSourceService($package_entry['type']);
      $this->packages[] = $sourceService->preparePackage($package_entry['source']);
      if (isset($package_entry['options']['extra-validation']) && $package_entry['options']['extra-validation']) {
        $cohesion_sync_import_options = &drupal_static('cohesion_sync_import_options');
        $cohesion_sync_import_options['extra-validation'] = TRUE;
      }
    }

    return $this;
  }

  /**
   * Creates Batch array for import and rebuild actions.
   *
   * @return array
   *   Batch array.
   *
   * @throws \Exception
   */
  protected function createImportBatch(): array {
    if (empty($this->packages)) {
      return [];
    }

    $batch_builder = $this->createBatchBuilder();

    foreach ($this->packages as $package) {
      if (isset($package) && is_dir($package) && !empty($this->fileSystem->scanDirectory($package, '/.*/'))) {
        $source_storage = new CohesionFileStorage($package);

        // Config entity replacements.
        foreach ($source_storage->listAll() as $name) {
          $data = $source_storage->read($name);
          $this->replacementStorage->replaceData($name, $data);
        }

        // Operations for handling file entity imports.
        $batch_builder->addOperation(
          [BatchImportController::class, 'fileImport'],
          [$source_storage, $package]
        );
      }
    }

    if ($this->createStorageComparer()->createChangelist()->hasChanges()) {

      $sync_steps = $this->createConfigImporter()->initialize();
      foreach ($sync_steps as $sync_step) {
        $batch_builder->addOperation(
          [ConfigImporterBatch::class, 'process'],
          [$this->configImporter, $sync_step]
        );
      }
      $batch_builder->addOperation(
        [BatchImportController::class, 'handleRebuilds'],
        [$this->storageComparer]
      );

      $recreates = $this->storageComparer->getRecreates();
      if (!empty($recreates)) {
        $batch_builder->addOperation(
          [BatchImportController::class, 'handleInuse'],
          [$recreates]
        );
      }

    }

    return $batch_builder->toArray();
  }

  /**
   * Creates instance of BatchBuilder with predefined values.
   *
   * @return \Drupal\Core\Batch\BatchBuilder
   *   BatchBuilder object.
   */
  protected function createBatchBuilder(): BatchBuilder {
    $batch_builder = new BatchBuilder();
    $batch_builder->setTitle($this->t('Importing package(s).'))
      ->setFinishCallback([BatchImportController::class, 'finish'])
      ->setInitMessage($this->t('Starting configuration synchronization.'))
      ->setProgressMessage($this->t('Starting configuration synchronization.'))
      ->setProgressive(FALSE)
      ->setErrorMessage($this->t('Configuration synchronization has encountered an error.'));

    return $batch_builder;
  }

  /**
   * Creates CohesionStorageComparer.
   *
   * @return \Drupal\cohesion_sync\Config\CohesionStorageComparer
   *   CohesionStorageComparer object.
   */
  protected function createStorageComparer(): CohesionStorageComparer {
    $this->storageComparer = new CohesionStorageComparer(
      $this->replacementStorage,
      $this->activeStorage,
      $this->usageUpdateManager
    );

    return $this->storageComparer;
  }

  /**
   * Creates ConfigImporter.
   *
   * @return \Drupal\Core\Config\ConfigImporter
   *   ConfigImporter object.
   */
  protected function createConfigImporter(): ConfigImporter {
    $this->configImporter = new ConfigImporter(
      $this->storageComparer,
      $this->eventDispatcher,
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

    return $this->configImporter;
  }

  /**
   * Checks if package entry contains required properties.
   *
   * @param array $package_entry
   *   Package definition.
   * @return void
   */
  protected function validatePackageEntry(array $package_entry) {
    foreach (self::REQUIRED_PROPERTIES as $required_property) {
      if (!isset($package_entry[$required_property])) {
        $missing_properties[] = $required_property;
      }
    }
    if (!empty($missing_properties)) {
      throw new PackageDefinitionMissingPropertiesException($missing_properties, self::REQUIRED_PROPERTIES);
    }
  }

}
