<?php

namespace Drupal\cohesion_sync\Drush;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\State\StateInterface;

/**
 * Command helpers.
 *
 * @package Drupal\cohesion_sync\Drush
 */
final class CommandHelpers {

  /**
   * @var null
   */
  protected static $instance = NULL;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * The uuid service
   *
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuidGenerator;

  /**
   * DrushCommandHelpers constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\cohesion_sync\PackagerManager $packagerManager
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   * @param \Drupal\Core\State\StateInterface $state
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, ConfigFactoryInterface $configFactory, PackagerManager $packagerManager, EntityRepositoryInterface $entityRepository, StateInterface $state, FileSystemInterface $fileSystem, UuidInterface $uuid) {
    $this->entityTypeManager = $entityTypeManager;
    $this->configFactory = $configFactory;
    $this->packagerManager = $packagerManager;
    $this->entityRepository = $entityRepository;
    $this->state = $state;
    $this->fileSystem = $fileSystem;
    $this->uuidGenerator = $uuid;

    /** @var \Drupal\Core\Config\ImmutableConfig config */
    $this->config = $this->configFactory->get('cohesion.sync.settings');
  }

  /**
   * @return string
   */
  private function getExportFilename() {
    // Get a filename safe version of the site name.
    $site_name = preg_replace('/[^a-z0-9]+/', '-', strtolower(\Drupal::config('system.site')->get('name')));
    return $site_name . '.package.yml_';
  }

  /**
   * Get the sync directory to use for import/export.
   *
   * @return mixed|string
   */
  private function getSyncDirectory() {
    $sync_directory_setting = Settings::get('site_studio_sync');

    if (!empty($sync_directory_setting)) {
      $dir = $sync_directory_setting;
    }
    else {
      $dir = 'sites/default/files/sync';
    }

    return $dir;
  }

  /**
   * Perform the export. Return success message.
   *
   * @param $filename_prefix
   *
   * @return bool|string
   *
   * @throws \Exception
   */
  public function exportAll($filename_prefix = FALSE) {

    // Make sure the config sync directory has been set.
    $dir = $this->getSyncDirectory();

    // Get the enabled entity types.
    $enabled_entity_types = $this->config->get('enabled_entity_types');
    if (!is_array($enabled_entity_types)) {
      throw new \Exception('Export settings have not been defined (enabled_entity_types configuration not found). Visit: /admin/cohesion/sync/export_settings to configure package export.');
    }

    // Build the excluded entity types up.
    $excluded_entity_type_ids = [];
    foreach ($enabled_entity_types as $entity_type_id => $enabled) {
      if (!$enabled) {
        $excluded_entity_type_ids[] = $entity_type_id;
      }
    }

    // Loop over each Site studio entity type to get all the entities.
    $entities = [];
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type => $definition) {
      if ($definition->entityClassImplements(CohesionSettingsInterface::class) && !in_array($entity_type, $excluded_entity_type_ids) && $entity_type !== 'custom_style_type') {
        try {
          $entity_storage = $this->entityTypeManager->getStorage($entity_type);
        }
        catch (\Exception $e) {
          continue;
        }

        /** @var \Drupal\Core\Config\Entity\ConfigEntityInterface $entity */
        foreach ($entity_storage->loadMultiple() as $entity) {
          if ($entity->status()) {
            $entities[] = $entity;
          }
        }
      }
    }

    // Check there are entities.
    if (!count($entities)) {
      return 'No Site Studio entities were found. Nothing was exported.';
    }

    // Prepare the directory.
    if (!$this->fileSystem->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      throw new \Exception('Unable to prepare directory: ' . $dir);
    }

    // Build the filename.
    if ($filename_prefix) {
      $filename = $filename_prefix . '.package.yml_';
    }
    else {
      $filename = $this->getExportFilename();
    }

    // Save the file.
    $file_destination = $dir . '/' . $filename;
    $fp = fopen($file_destination, 'w');

    // Use the Yaml generator to stream the output to the file
    // (buildPackageStream() yields).
    $counter = 0;
    foreach ($this->packagerManager->buildPackageStream($entities, TRUE, $excluded_entity_type_ids) as $yaml) {
      fwrite($fp, $yaml);
      $counter++;
    }

    fclose($fp);

    return 'Exported ' . $counter . ' items to ' . $file_destination;
  }

  /**
   *
   * Import a package from a path or the Site Studio sync directory
   *
   * @param bool $overwrite
   * @param bool $keep
   * @param string|NULL $path
   * @param bool $force
   * @param bool $no_rebuild
   *   True if no entity rebuilds are required.
   * @param bool $no_maintenance
   *
   * @throws \Exception
   *
   * @return array The batch operations
   */
  public function import($overwrite, $keep, $path, $force = FALSE, $no_rebuild = FALSE, $no_maintenance = FALSE) {

    $paths = [];

    // Path was specified by the user.
    if ($path !== NULL) {
      $paths[] = $path;
    }
    // Full import (no paths specified, so look up from settings.php)
    else {

      // Make sure the config sync directory has been set.
      $dir = $this->getSyncDirectory();

      try {
        $this->fileSystem->scanDirectory($dir, '/.package.yml_$/', [
          'callback' => function ($file) use (&$paths, &$messages) {
            $paths[] = $file;
          },
        ]);
      }
      catch (\Throwable $e) {
        \Drupal::messenger()->addError(t('Unable to scan directory for sync import.'));
      }

      if (empty($paths)) {
        throw new \Exception('No *.package.yml_ files found in ' . $dir);
      }
    }

    $batch_operations = [];
    $store_key = 'drush_sync_validation' . $this->uuidGenerator->generate();

    // Import each file in the paths list.
    foreach ($paths as $path) {
      $batch_operations = array_merge($batch_operations, $this->packagerManager->validatePackageBatch($path, $store_key));
    }

    foreach ($paths as $path) {
      $batch_operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::setImportBatch',
        [
          $path,
          $store_key,
          $overwrite,
          $keep,
          $force,
          $no_rebuild,
          $no_maintenance,
        ],
      ];
    }

    $operations[] = [
      '\Drupal\cohesion_sync\Controller\BatchImportController::batchDrushValidationFinishedCallback',
      [$no_maintenance],
    ];

    return $batch_operations;
  }

}
