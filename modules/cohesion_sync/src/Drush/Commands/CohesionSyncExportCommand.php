<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Config\CohesionFullPackageStorage;
use Drupal\cohesion_sync\Config\CohesionPackageStorage;
use Drupal\cohesion_sync\Entity\Package;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\FileStorage;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Site\Settings;
use Drush\Commands\DrushCommands;
use Drush\Exceptions\UserAbortException;
use Consolidation\AnnotatedCommand\CommandResult;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Site Studio export drush command.
 */
class CohesionSyncExportCommand extends DrushCommands {

  /**
   * EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Config Storage service.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Cohesion Full Package Storage service.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionFullPackageStorage
   */
  protected $cohesionFullPackageStorage;

  /**
   * Cohesion Package Storage service.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionPackageStorage
   */
  protected $cohesionPackageStorage;

  /**
   * Destination directory.
   *
   * @var string
   */
  protected $destinationDir;

  /**
   * The configuration manager.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * CohesionSyncCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   Config Storage service.
   * @param \Drupal\cohesion_sync\Config\CohesionFullPackageStorage $cohesionFullPackageStorage
   *   Cohesion full package storage service.
   * @param \Drupal\cohesion_sync\Config\CohesionPackageStorage $cohesionPackageStorage
   *   Cohesion package storage service.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    StorageInterface $config_storage,
    CohesionFullPackageStorage $cohesionFullPackageStorage,
    CohesionPackageStorage $cohesionPackageStorage,
    ConfigManagerInterface $config_manager,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configStorage = $config_storage;
    $this->cohesionFullPackageStorage = $cohesionFullPackageStorage;
    $this->cohesionPackageStorage = $cohesionPackageStorage;
    $this->configManager = $config_manager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('entity_type.manager'),
      $container->get('config.storage'),
      $container->get('cohesion_sync.full_package_storage'),
      $container->get('cohesion_sync.package_storage'),
      $container->get('config.manager')
    );
  }

  /**
   * Export Site studio package files to a path.
   *
   * @option package
   *   Site studio Package id of specific package, on NULL full export executed.
   * @option path
   *   Target directory, on NULL defaults to $settings['site_studio_sync'].
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sitestudio:package:export
   * @aliases cohesion:package:export
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function siteStudioExport(
    array $options = [
      'package' => NULL,
      'path' => NULL,
    ],
  ) {

    try {
      $this->setTargetDirectory($options['path']);
      $source_storage = $this->getSourceStorage($options['package']);
      $target_storage = $this->getTargetStorage();
    }
    catch (\Exception $e) {
      return CommandResult::dataWithExitCode($e->getMessage(), self::EXIT_FAILURE);
    }

    $exported_config = 0;
    foreach ($source_storage->listAll() as $name) {
      $data = $source_storage->read($name);
      $target_storage->write($name, $data);
      $exported_config++;
    }
    $exported_files = $target_storage->exportFiles($source_storage->getStorageFileList());

    $this->yell(sprintf('Exported %s config and %s non-config files.', $exported_config, $exported_files));
  }

  /**
   * Returns Storage based on parameter or sitestudio settings.
   *
   * @param string|null $package
   *   Package name.
   *
   * @return \Drupal\cohesion_sync\Config\CohesionPackageStorage
   *   CohesionPackageStorage.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function getSourceStorage(?string $package = NULL): StorageInterface {
    // If a package is specified, get the storage for that package.
    if ($package) {
      $package_entity = $this->entityTypeManager->getStorage('cohesion_sync_package')->load($package);
      if ($package_entity instanceof Package) {
        $this->cohesionPackageStorage->setPackage($package_entity);
        $source_storage = $this->cohesionPackageStorage;
      }
      else {
        throw new \Exception('Cannot find package with id: ' . $package);
      }
    }
    else {
      $source_storage = $this->cohesionFullPackageStorage;
    }

    return $source_storage;
  }

  /**
   * Sets destinationDir property based on argument or SiteStudio settings.
   *
   * @param string|null $destination_dir
   *   Destination directory.
   *
   * @throws \Exception
   */
  protected function setTargetDirectory(?string $destination_dir = NULL) {
    $destination_dir = $destination_dir ?: Settings::get('site_studio_sync', COHESION_SYNC_DEFAULT_DIR);
    if ($destination_dir === NULL) {
      throw new \Exception('No destination directory provided and no value set in `site_studio_sync` settings.');
    }
    $this->destinationDir = $destination_dir;
    if (substr($this->destinationDir, -1) !== '/') {
      $this->destinationDir .= '/';
    }
  }

  /**
   * Gets FileStorage.
   *
   * @throws \Drush\Exceptions\UserAbortException
   * @throws \Exception
   */
  protected function getTargetStorage(): FileStorage {
    $target_storage = new CohesionFileStorage($this->destinationDir);
    $destination_dir = is_dir($this->destinationDir) ? new \FilesystemIterator($this->destinationDir) : FALSE;
    if (is_iterable($destination_dir) && iterator_count($destination_dir) > 0) {
      if (!$this->io()->confirm(t('Files in your export directory (@target) will be deleted and replaced with the package config and files.', ['@target' => $this->destinationDir]))) {
        throw new UserAbortException();
      }

      $target_storage->deleteAll();

      // Also delete collections.
      foreach ($target_storage->getAllCollectionNames() as $collection_name) {
        $target_collection = $target_storage->createCollection($collection_name);
        $target_collection->deleteAll();
      }
    }

    return $target_storage;
  }

}
