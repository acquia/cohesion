<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\UsagePluginManager;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Config\CohesionFullPackageStorage;
use Drupal\cohesion_sync\Config\CohesionIndividualEntityPackageStorage;
use Drupal\cohesion_sync\Config\CohesionPackageStorage;
use Drupal\cohesion_sync\Entity\Package;
use Drupal\cohesion_sync\PackagerManager;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

/**
 * Handles Package Export Generation.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class PackageExportGenerateController extends ControllerBase {

  use DependencySerializationTrait;

  const FILE_GENERATED_STATE_KEY = 'cohesion_sync.export_file_generated.%s';

  /**
   * The file system.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Module Handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The state store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Site name in a format suitable for filename.
   *
   * @var string
   */
  protected $fullPackageFileName;

  /**
   * Date Formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Packager service.
   *
   * @var \Drupal\cohesion_sync\PackagerManager
   */
  protected $packagerManager;

  /**
   * Entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Usage manager service.
   *
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * Cohesion file storage service.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionFileStorage
   */
  protected $cohesionFileStorage;

  /**
   * Full package or individual package storage.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionFullPackageStorage
   */
  protected $cohesionFullPackageStorage;

  /**
   * Package based on individual entity storage.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionIndividualEntityPackageStorage
   */
  protected $cohesionIndividualEntityPackageStorage;

  /**
   * Full package batch export limit.
   *
   * @var int|null
   */
  protected $fullPackageBatchLimit;

  /**
   * Individual package batch export limit.
   *
   * @var int|null
   */
  protected $packageBatchLimit;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack|null
   */
  protected $requestStack;

  /**
   * Current storage.
   *
   * @var \Drupal\cohesion_sync\Config\CohesionFullPackageStorage|\Drupal\cohesion_sync\Config\CohesionPackageStorage
   *   Package storage or Full package storage.
   */
  protected $currentStorage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.storage'),
      $container->get('cohesion_sync.packager'),
      $container->get('entity.repository'),
      $container->get('plugin.manager.usage.processor'),
      $container->get('file_system'),
      $container->get('cohesion_sync.file_storage'),
      $container->get('cohesion_sync.full_package_storage'),
      $container->get('cohesion_sync.package_storage'),
      $container->get('cohesion_sync.individual_entity_package_storage'),
      $container->get('module_handler'),
      $container->get('state'),
      $container->get('config.manager'),
      $container->get('request_stack')
    );
  }

  /**
   * ExportFormBase constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_manager
   *   Entity Manager.
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   Config storage.
   * @param \Drupal\cohesion_sync\PackagerManager $packager_manager
   *   Package manager.
   * @param \Drupal\Core\Entity\EntityRepository $entity_repository
   *   Entity Repository.
   * @param \Drupal\cohesion\UsagePluginManager $usage_plugin_manager
   *   Usage plugin manager.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system.
   * @param \Drupal\cohesion_sync\Config\CohesionFileStorage $cohesion_file_storage
   *   Cohesion file storage service.
   * @param \Drupal\cohesion_sync\Config\CohesionFullPackageStorage $cohesion_full_package_storage
   *   Cohesion full package storage service.
   * @param \Drupal\cohesion_sync\Config\CohesionPackageStorage $cohesion_package_storage
   *   Cohesion package storage service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module Handler service.
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key value store.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   The configuration manager.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   Request stack.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_manager,
    StorageInterface $config_storage,
    PackagerManager $packager_manager,
    EntityRepository $entity_repository,
    UsagePluginManager $usage_plugin_manager,
    FileSystemInterface $file_system,
    CohesionFileStorage $cohesion_file_storage,
    CohesionFullPackageStorage $cohesion_full_package_storage,
    CohesionPackageStorage $cohesion_package_storage,
    CohesionIndividualEntityPackageStorage $cohesion_individual_entity_package_storage,
    ModuleHandlerInterface $moduleHandler,
    StateInterface $state,
    ConfigManagerInterface $config_manager,
    RequestStack $requestStack
  ) {
    $this->entityTypeManager = $entity_manager;
    $this->configStorage = $config_storage;
    $this->packagerManager = $packager_manager;
    $this->entityRepository = $entity_repository;
    $this->fullPackageBatchLimit = $this->config('cohesion.sync.settings')->get('full_export_limit');
    $this->packageBatchLimit = $this->config('cohesion.sync.settings')->get('package_export_limit');
    $this->usagePluginManager = $usage_plugin_manager;
    $this->fileSystem = $file_system;
    $this->cohesionFileStorage = $cohesion_file_storage;
    $this->cohesionFullPackageStorage = $cohesion_full_package_storage;
    $this->cohesionPackageStorage = $cohesion_package_storage;
    $this->cohesionIndividualEntityPackageStorage = $cohesion_individual_entity_package_storage;
    $this->moduleHandler = $moduleHandler;
    $this->state = $state;
    $site_name = preg_replace('/[^a-z0-9]+/', '-', strtolower($this->config('system.site')->get('name')));
    $this->fullPackageName = $site_name;
    $this->configManager = $config_manager;
    $this->requestStack = $requestStack;
  }

  /**
   * Handles fle download from temporary storage.
   *
   * @param string $entity_type
   *   Entity type.
   * @param string $entity_uuid
   *   Entity uuid.
   */
  public function exportSingleEntityPackage(string $entity_type, string $entity_uuid) {

    $results = $this->entityTypeManager->getStorage($entity_type)->loadByProperties(['uuid' => $entity_uuid]);
    $entity = reset($results);

    if (!$entity instanceof CohesionConfigEntityBase) {
      $this->messenger()->addError($this->t('Entity :type with UUID :uuid does not exist or is not available for export.', [
        ':type' => $entity_type,
        ':uuid' => $entity_uuid,
      ]));
      $destination = $this->requestStack->getCurrentRequest()->get('destination', Url::fromRoute('<front>')->toString());

      return new RedirectResponse($destination);
    }

    $this->deleteFile($this->getPackageFileUri($entity->id()));
    $this->cohesionIndividualEntityPackageStorage->setEntity($entity);
    $config = $this->cohesionIndividualEntityPackageStorage->listAll();
    $files = $this->cohesionIndividualEntityPackageStorage->getStorageFileList();

    $archiver = new ArchiveTar($this->getPackageFileUri($entity->id()), 'gz');

    foreach ($files as $uuid => $type) {
      $file_entity = $this->entityTypeManager->getStorage('file')->loadByProperties(['uuid' => $uuid]);
      $file = reset($file_entity);
      if ($file instanceof FileInterface) {
        $entry = $this->buildFileExportEntry($file);
        $data = SymfonyYaml::dump($entry, PHP_INT_MAX, 2, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE + SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
        if (!file_exists($file->getFileUri())) {
          $this->deleteFile($this->getPackageFileUri($entity->id()));
          $this->messenger()->addError($this->t('Cannot export %entity_label entity. File %filename does not exists or is not readable.', [
            '%entity_label' => $entity->label(),
            '%filename' => $file->getFilename(),
          ]));
          $destination = $this->requestStack->getCurrentRequest()->get('destination', Url::fromRoute('<front>')->toString());

          return new RedirectResponse($destination);
        }
        $content = file_get_contents($file->getFileUri());
        $archiver->addString($file->getFilename(), $content, $file->getCreatedTime());
        $archiver->addString(CohesionFileStorage::FILE_METADATA_PREFIX . '.' . $file->uuid() . '.' . CohesionFileStorage::getFileExtension(), $data);
      }
    }
    foreach ($config as $name) {
      $config_item = $this->cohesionIndividualEntityPackageStorage->read($name);
      $archiver->addString("$name.yml", $this->cohesionFileStorage->encode($config_item));
    }

    return new RedirectResponse(Url::fromRoute('cohesion_sync.export_all.download', ['filename' => $entity->id() . '.tar.gz'])->toString());
  }

  /**
   * Handles fle download from temporary storage.
   *
   * @param string $package
   *   Package machine name.
   */
  public function generatePackage(string $package) {
    $this->state->delete(self::getPackageStateKey($package));
    $this->deleteFile($this->getPackageFileUri($package));

    $package_entity = $this->entityTypeManager->getStorage('cohesion_sync_package')->load($package);
    if (!$package_entity instanceof Package) {
      throw new \Exception('Cannot find package with id: ' . $package);
    }

    $batch = $this->createPackageBatch($package_entity);
    batch_set($batch);

    return batch_process(Url::fromRoute('cohesion_sync.export.export_package', ['package' => $package]));
  }

  /**
   * Generates Package Export batch.
   */
  public function generateFullPackage() {
    $this->state->delete(self::getPackageStateKey($this->fullPackageName));
    $this->deleteFile($this->getPackageFileUri($this->fullPackageName));

    $batch = $this->createFullPackageBatch();
    batch_set($batch);

    return batch_process(Url::fromRoute('cohesion_sync.export_all'));
  }

  /**
   * Processes export batch.
   *
   * @param array $package
   *   Array with package name and URI.
   * @param int $index
   *   Batch index.
   * @param array $batch
   *   Current batch operations set.
   * @param array $context
   *   Batch context.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function processExportBatch(array $package, int $index, array $batch, array &$context) {
    // Do not process if there is an error
    if (isset($context['results']['error']) && $context['results']['error'] !== '') {
      return;
    }

    if ($index === 0) {
      if (!isset($context['results']['package'])) {
        $context['results']['package'] = $package;
      }
      $this->deleteFile($package['uri']);
      $context['sandbox'] = [];
      $context['sandbox']['progress'] = 0;
    }
    $archiver = new ArchiveTar($package['uri'], 'gz');

    foreach ($batch as $key => $value) {
      if (Uuid::isValid($key) && $value === 'file') {
        $file = $this->entityTypeManager->getStorage('file')->loadByProperties(['uuid' => $key]);
        $file = reset($file);
        if ($file instanceof FileInterface) {
          $entry = $this->buildFileExportEntry($file);
          $data = SymfonyYaml::dump($entry, PHP_INT_MAX, 2, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE + SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
          if (!file_exists($file->getFileUri())) {
            $this->deleteFile($this->getPackageFileUri($package['uri']));
            $context['results']['error'] = $this->t('Cannot export package. File %filename does not exists or is not readable.', [
              '%filename' => $file->getFilename(),
            ]);
          }
          $content = file_get_contents($file->getFileUri());
          $archiver->addString($file->getFilename(), $content, $file->getCreatedTime());
          $archiver->addString(CohesionFileStorage::FILE_METADATA_PREFIX . '.' . $file->uuid() . '.' . CohesionFileStorage::getFileExtension(), $data);
        }
      }
      elseif ($config_item = $this->currentStorage->read($value)) {
        $archiver->addString("$value.yml", $this->cohesionFileStorage->encode($config_item));
      }
    }

    $context['message'] = t('Running batch @index - Processing @count entities in this batch.',
      [
        '@index' => $index + 1,
        '@count' => count($batch),
      ]
    );
    if (isset($context['sandbox']['progress'])) {
      $context['sandbox']['progress']++;
    }
    else {
      $context['sandbox']['progress'] = $index;
    }
  }

  /**
   * Package export finished callback.
   *
   * @param bool $success
   *   Successful operations.
   * @param array $results
   *   Metadata required for generating exported files index.
   */
  public function packageExportFinished(bool $success, array $results) {

    if (isset($results['error']) && $results['error'] !== '') {
      $this->messenger()->addError($results['error']);
    }
    elseif ($success) {
      $this->state->set(sprintf(self::getPackageStateKey($results['package']['name'])), TRUE);
      $this->messenger()->addStatus($this->t('Package file has been successfully generated.'));
    }

  }

  /**
   * Removes package file from temporary directory.
   *
   * @param string $package
   *   Package name.
   */
  public function remove(string $package) {
    $this->deleteFile($this->getPackageFileUri($package));
    $this->state->delete(self::getPackageStateKey($package));
    $this->messenger()->addStatus($this->t('Package file has been successfully removed.'));
    $destination = $this->requestStack->getCurrentRequest()->get('destination', Url::fromRoute('entity.cohesion_sync_package.collection')->toString());

    return new RedirectResponse($destination);
  }

  /**
   * Attempts to delete existing package file.
   */
  private function deleteFile(string $uri) {
    try {
      $this->fileSystem->delete($uri);
    }
    catch (FileException $e) {
      // Ignore failed deletes.
    }
  }

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  protected function buildFileExportEntry($entity) {
    /** @var \Drupal\file\Entity\File $entity */
    $struct = [];

    // Get all the field values into the struct.
    foreach ($entity->getFields() as $field_key => $value) {
      if ($field_key === 'fid') {
        continue;
      }
      // Get the value of the field.
      if ($value = $entity->get($field_key)->getValue()) {
        $value = reset($value);
      }
      else {
        continue;
      }

      // Add it to the export.
      if (isset($value['value'])) {
        $struct[$field_key] = $value['value'];
      }
    }

    return $struct;
  }

  /**
   * Get Package File URI in temporary directory.
   *
   * @param string $name
   *   Package name.
   *
   * @return string
   *   Gets file URI in temporary directory.
   */
  protected function getPackageFileUri(string $name): string {
    return 'temporary://' . $name . '.tar.gz';
  }

  /**
   * Creates batch definition.
   *
   * @param string $package_name
   *   Package name.
   * @param int $limit
   *   Batch limit.
   *
   * @return array
   *   Batch definition.
   */
  protected function createBatch(string $package_name, int $limit): array {
    $config = $this->currentStorage->listAll();
    $files = $this->currentStorage->getStorageFileList();
    $entities = array_merge($config, $files);
    $result = array_chunk($entities, $limit, TRUE);
    $num_operations = count($result);

    $package = [
      'name' => $package_name,
      'uri' => $this->getPackageFileUri($package_name),
    ];

    $operations = [];
    foreach ($result as $key => $value) {
      $operations[] = [
        [$this, 'processExportBatch'],
        [$package, $key, $value],
      ];
    }
    return [
      'title' => $this->t('Running @num batches to process @count entities.', [
        '@num' => $num_operations,
        '@count' => count($entities),
      ]),
      'operations' => $operations,
      'finished' => [$this, 'packageExportFinished'],
    ];
  }

  /**
   * Creates batch definition for package export.
   *
   * @param \Drupal\cohesion_sync\Entity\Package $package
   *   Package name.
   *
   * @return array
   *   Batch definition.
   */
  protected function createPackageBatch(Package $package): array {
    $this->cohesionPackageStorage->setPackage($package);

    $limit = $this->packageBatchLimit ?? 10;
    $this->currentStorage = $this->cohesionPackageStorage;

    return $this->createBatch($package->id(), $limit);
  }

  /**
   * Creates batch definition for full package export.
   *
   * @return array
   *   Batch definition.
   */
  protected function createFullPackageBatch(): array {
    $limit = $this->packageBatchLimit ?? 10;

    $this->currentStorage = $this->cohesionFullPackageStorage;
    return $this->createBatch($this->fullPackageName, $limit);
  }

  /**
   * Generates State API key for package name.
   *
   * @param string $name
   *   Package name.
   *
   * @return string
   *   State API key for given package.
   */
  public static function getPackageStateKey(string $name): string {
    return sprintf(self::FILE_GENERATED_STATE_KEY, $name);
  }

}
