<?php

namespace Drupal\cohesion_sync;

use Drupal\cohesion\Entity\ContentIntegrityInterface;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\Entity\Package;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystem;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Serialization\Yaml;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Yaml\Dumper;
use Symfony\Component\Yaml\Yaml as SymfonyYaml;

define('ENTRY_NEW_IMPORTED', 1);
define('ENTRY_EXISTING_ASK', 2);
define('ENTRY_EXISTING_OVERWRITTEN', 3);
define('ENTRY_EXISTING_IGNORED', 4);
define('ENTRY_EXISTING_LOCKED', 5);
define('ENTRY_EXISTING_NO_CHANGES', 6);

/**
 * Class PackagerManager.
 *
 * Defines cohesion PackagerManager.
 *
 * @package Drupal\cohesion_sync
 */
class PackagerManager {

  /**
   * Holds the entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Holds the entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\cohesion_sync\SyncPluginManager
   */
  protected $syncPluginManager;

  /**
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @var \Drupal\Core\File\FileSystem
   */
  protected $fileSystem;

  /**
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected $extensionListModule;

  /**
   * The entries extracted from the last import.
   *
   * @var array
   */
  protected $entries = [];

  /**
   * PackagerManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\cohesion_sync\SyncPluginManager $sync_plugin_manager
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   * @param \Drupal\Core\File\FileSystem $file_system
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   */
  public function __construct(EntityRepository $entityRepository, EntityTypeManagerInterface $entityTypeManager, SyncPluginManager $sync_plugin_manager, UsageUpdateManager $usage_update_manager, FileSystem $file_system, LoggerChannelFactoryInterface $logger_factory, StorageInterface $config_storage, ConfigFactoryInterface $config_factory) {
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->syncPluginManager = $sync_plugin_manager;
    $this->usageUpdateManager = $usage_update_manager;
    $this->fileSystem = $file_system;
    $this->logger = $logger_factory->get('cohesion_sync');
    $this->configStorage = $config_storage;
    $this->configFactory = $config_factory;
  }

  /**
   * Find a matching SyncPlugin plugin for this entity.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type_definition
   *
   * @return null
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getPluginInstanceFromType(EntityTypeInterface $entity_type_definition) {
    // Loop through all the plugin definitions.
    foreach ($this->syncPluginManager->getDefinitions() as $id => $definition) {
      // Create a reflection class to see if this entity implements in the interface in the annotation.
      if (in_array($definition['interface'], class_implements($entity_type_definition->getClass()))) {
        // Found a match.
        return $this->syncPluginManager->createInstance($id)
          ->setType($entity_type_definition);
      }
    }

    return NULL;
  }

  /**
   * Loads and decodes a Yaml file and runs the callback on each root entry.
   *
   * @param $uri
   * @param $callback
   *
   * @return bool
   */
  private function parseYaml($uri, $callback) {
    if ($handle = @ fopen($uri, 'r')) {
      $yaml = '';

      while (!feof($handle)) {
        $line = fgets($handle);

        // Hit the end of an array entry.
        if ($yaml != '' && $line == "-\n") {
          $entry = Yaml::decode($yaml)[0];
          $callback($entry);
          $yaml = $line;
        }
        // Building up an array entry.
        else {
          $yaml .= $line;
        }
      }

      $entry = Yaml::decode($yaml)[0];
      $callback($entry);

      fclose($handle);
    }
  }

  /**
   * @param $uri
   * @param $uuid
   * @return mixed|null
   */
  public function getExportByUUID($uri, $uuid) {
    if ($handle = @ fopen($uri, 'r')) {
      $yaml = '';

      $entry = NULL;
      while (!feof($handle) && $entry == NULL) {
        $line = fgets($handle);

        // Hit the end of an array entry.
        if ($yaml != '' && $line == "-\n") {
          $entry_data = Yaml::decode($yaml)[0];
          if($entry_data['export']['uuid'] == $uuid) {
            $entry = $entry_data;
          }
          $yaml = $line;
        }
        // Building up an array entry.
        else {
          $yaml .= $line;
        }
      }

      if($entry == NULL) {
        $entry_data = Yaml::decode($yaml)[0];
        if($entry_data['export']['uuid'] == $uuid) {
          $entry = $entry_data;
        }
      }

      fclose($handle);

      return $entry;
    }
  }

  public function getExportsByUUID($uri, $uuids) {
    if ($handle = @ fopen($uri, 'r')) {
      $yaml = '';

      $entries = [];
      while (!feof($handle) && count($entries) != count($uuids)) {
        $line = fgets($handle);

        // Hit the end of an array entry.
        if ($yaml != '' && $line == "-\n") {
          $entry_data = Yaml::decode($yaml)[0];
          if(in_array($entry_data['export']['uuid'], $uuids)) {
            $entries[] = $entry_data;
          }
          $yaml = $line;
        }
        // Building up an array entry.
        else {
          $yaml .= $line;
        }
      }

      $entry_data = Yaml::decode($yaml)[0];
      if(in_array($entry_data['export']['uuid'], $uuids)) {
        $entries[] = $entry_data;
      }

      fclose($handle);

      return $entries;
    }
  }

  /**
   * Validate a package list via the plugin and return the actions.
   *
   * @param $entry
   *
   * @throws \Exception
   */
  public function validatePackageEntry($entry) {
    // Get the Sync plugin for this entity type.
    try {
      $type_definition = $this->entityTypeManager->getDefinition($entry['type']);
      /** @var SyncPluginInterface $plugin */
      $plugin = $this->getPluginInstanceFromType($type_definition);
    }
    catch (\Exception $e) {
      throw new \Exception("Entity type {$entry['type']} not found.");
    }

    // Check to see if the entry can be applied without asking what to do.
    $action_state = $plugin->validatePackageEntryShouldApply($entry['export']);

    return $plugin->getActionData($entry['export'], $action_state);
  }

  /**
   * Sets the batch for validating a package.
   *
   * @param $file_uri string
   * @param $store_key string
   *
   * @return array - operations for the batch
   */
  public function validatePackageBatch($file_uri, $store_key) {

    $operations = [];

    $operations[] = [
      '\Drupal\cohesion_sync\Controller\BatchImportController::batchValidatePackage',
      [$file_uri],
    ];

    $entity_uuids_to_validate = [];
    $this->parseYaml($file_uri, function ($entry) use (&$operations, $file_uri, $store_key, &$entity_uuids_to_validate) {
      $entity_uuids_to_validate[] = $entry['export']['uuid'];
    });

    $max_validation = 10;

    for ($i=0; $i < count($entity_uuids_to_validate); $i += $max_validation) {
      $ids = array_slice($entity_uuids_to_validate, $i, $max_validation);
      $operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::batchValidateEntry',
        [$file_uri, $ids, $store_key],
      ];
    }

    return $operations;
  }

  /**
   * Apply a package entry to the site.
   *
   * @param $entry
   *
   * @throws \Exception
   */
  public function applyPackageEntry($entry) {
    // Get the Sync plugin for this entity type.
    try {
      $type_definition = $this->entityTypeManager->getDefinition($entry['type']);
      /** @var SyncPluginInterface $plugin */
      $plugin = $this->getPluginInstanceFromType($type_definition);
    }
    catch (\Exception $e) {
      throw new \Exception("Entity type {$entry['type']} not found.");
    }

    // Check to see if the entry can be applied without asking what to do.
    $plugin->applyPackageEntry($entry['export']);
  }

  /**
   * @param array $entry
   *   Package entry.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function postApplyPackageEntry($entry) {
    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');
    $cohesion_sync_lock = FALSE;

    // Get the imported entity to work on.
    if ($entity = $this->entityRepository->loadEntityByUuid($entry['type'], $entry['export']['uuid'])) {
      // Send to API and re-calculate in-use table.
      try {
        if (method_exists($entity, 'process')) {
          if (!method_exists($entity, 'status') || $entity->status()) {
            $entity->process();
          }
        }
        $this->usageUpdateManager->buildRequires($entity);
      }
      catch (\Exception $e) {
      }
    }
  }

  /**
   * Scan a Yaml package stream and create a batch array for the entries in
   * $action_data.
   *
   * @param $uri
   * @param $action_data
   * @param bool $no_rebuild
   *   True if no entity rebuilds required.
   *
   * @return array|bool
   */
  public function applyBatchYamlPackageStream($uri, $action_data, $no_rebuild = FALSE) {

    $operations = [];

    $uuids = [];
    foreach ($action_data as $uuid => $action) {
      if(in_array($action['entry_action_state'], [
        ENTRY_NEW_IMPORTED,
        ENTRY_EXISTING_OVERWRITTEN,
      ])) {
        $uuids[] = $uuid;
      }
    }

    $max_validation = 10;
    $uuids_batch = [];
    for ($i=0; $i < count($uuids); $i += $max_validation) {
      $uuids_batch[] = array_slice($uuids, $i, $max_validation);
    }

    foreach ($uuids_batch as $uuids) {
      $operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::batchAction',
        [$uri, $uuids],
      ];
    }

    foreach ($uuids_batch as $uuids) {
      $operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::batchConfigImport',
        [$uri, $uuids, $action_data],
      ];
    }

    if ($no_rebuild !== TRUE) {
      foreach ($action_data as $uuid => $action) {
        if(in_array($action['entry_action_state'], [
          ENTRY_NEW_IMPORTED,
          ENTRY_EXISTING_OVERWRITTEN,
        ])) {
          // Add  item to the batch.
          $operations[] = [
            '\Drupal\cohesion_sync\Controller\BatchImportController::batchPostAction',
            [$uri, $uuid, $action_data],
          ];
        }
      }
    }

    // Apllies only if there has been some imports
    if(!empty($uuids_batch)) {
      $operations[] = [
        'cohesion_elements_get_elements_style_process_batch',
        [],
      ];

      // Generate content template entities for any new entity type / bundle / view mode.
      $operations[] = [
        '_cohesion_templates_generate_content_template_entities',
        [],
      ];
    }

    return $operations;
  }

  /**
   * Set the data to be replaced int the storage replace.
   *
   * @param \Drupal\config\StorageReplaceDataWrapper $source_storage
   * @param array $entry
   * @param array $action_data
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function replaceData(&$source_storage, $entry, $action_data) {
    $type_definition = $this->entityTypeManager->getDefinition($entry['type']);
    // Only applies to config entities (ie: excludes files)
    if ($type_definition instanceof ConfigEntityType) {

      $config_id = $entry['export'][$type_definition->getKey('id')];
      if($type_definition->id() == 'cohesion_custom_style') {
        $custom_style_ids = \Drupal::entityQuery('cohesion_custom_style')
          ->condition('class_name', $entry['export']['class_name'])
          ->execute();

        if($custom_style_ids && !in_array($config_id, $custom_style_ids)) {
          $config_id = end($custom_style_ids);
        }
      }

      $config_name = $type_definition->getConfigPrefix() . '.' . $config_id;
      $entry['export'] = $this->matchUUIDS($action_data, $entry['export']);
      $source_storage->replaceData($config_name, $entry['export']);
    }
  }

  /**
   * @param $source_storage
   *
   * @return \Drupal\cohesion_sync\SyncConfigImporter
   */
  public function getConfigImporter($source_storage) {
    $storage_comparer = new StorageComparer(
      $source_storage,
      $this->configStorage,
      \Drupal::service('config.manager')
    );

    $storage_comparer->createChangelist();

    // Get the config importer.
    return new SyncConfigImporter(
      $storage_comparer,
      \Drupal::service('event_dispatcher'),
      \Drupal::service('config.manager'),
      \Drupal::service('lock.persistent'),
      \Drupal::service('config.typed'),
      \Drupal::service('module_handler'),
      \Drupal::service('module_installer'),
      \Drupal::service('theme_handler'),
      \Drupal::service('string_translation'),
      \Drupal::service('extension.list.module')
    );
  }

  /**
   * Replace uuids in an entry with the ones on the current site.
   *
   * @param array $action_data
   * @param array $entry
   *
   * @return array
   */
  public function matchUUIDS($action_data, $entry) {
    $replace_uuid = [];
    foreach ($action_data as $uuid => $data) {
      if (in_array($data['entry_action_state'], [ENTRY_NEW_IMPORTED, ENTRY_EXISTING_OVERWRITTEN]) && isset($data['replace_uuid'])) {
        $replace_uuid[$uuid] = $data['replace_uuid'];
      }
    }

    if (!empty($replace_uuid)) {
      $replaced_entry = str_replace(array_keys($replace_uuid), $replace_uuid, Yaml::encode($entry));
      $entry = Yaml::decode($replaced_entry);
    }

    return $entry;
  }

  /**
   * Generator for streaming package contents.
   *
   * @param $entities
   * @param $yaml
   * @param $exclude_entity_type_ids
   *
   * @return \Generator
   *
   * @throws \Exception
   */
  public function buildPackageStream($entities, $yaml = FALSE, $exclude_entity_type_ids = []) {
    // Loop over the dependencies including the source entity.
    foreach ($this->buildPackageEntityList($entities, $exclude_entity_type_ids) as $entry) {
      if ($entry) {
        // Get the plugin for this entity type.
        /** @var SyncPluginInterface $plugin */
        if ($plugin = $this->getPluginInstanceFromType($this->entityTypeManager->getDefinition($entry['type']))) {
          // Yield the yaml encoded export.
          $item = [
            'type' => $entry['type'],
            'export' => $plugin->buildExport($entry['entity']),
          ];

          if ($yaml) {
            if (is_array($item)) {
              $item = $this->processYamlData($item);
            }

            yield $this->yamlEncode([$item]);
          }
          else {
            yield $item;
          }
        }
      }
    }
  }

  /**
   * Get the redirect destination. Handles no input.
   *
   * @return string
   */
  private function getDestination() {
    $current_path = \Drupal::service('path.current')->getPath();
    $destination = \Drupal::destination()->get();

    return $current_path !== $destination ? $destination : '/admin/cohesion';
  }

  /**
   * User downloads a Yaml package from their browser.
   * This first saves the file to temporary:// so we can check for any errors.
   *
   * @param $filename
   * @param $entities
   * @param array $exclude_entity_type_ids
   *
   * @return \Symfony\Component\HttpFoundation\BinaryFileResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function sendYamlDownload($filename, $entities, $exclude_entity_type_ids = []) {
    // Stream the package to a temporary file.
    $tmp_stream_wrapper = Settings::get('coh_temporary_stream_wrapper', 'temporary://');
    $temp_file_path = $this->fileSystem->tempnam($tmp_stream_wrapper, 'package');
    $temp_file = fopen($temp_file_path, 'wb');

    if ($temp_file) {
      try {
        // Use the Yaml generator to stream the output to the file.
        foreach ($this->buildPackageStream($entities, TRUE, $exclude_entity_type_ids) as $yaml) {
          // Write to the temporary file.
          if (fwrite($temp_file, $yaml) === FALSE) {
            \Drupal::messenger()->addError(t('Unable to write to temporary file "%path"', ['%path' => $temp_file_path]));
            return new RedirectResponse(Url::fromUserInput($this->getDestination())
              ->toString());
          }
        }

      }
      catch (\Throwable $e) {
        fclose($temp_file);
        \Drupal::messenger()->addError(t('Package %path failed to build. There was a problem exporting the package. %e', [
          '%path' => $filename,
          '%e' => $e->getMessage(),
        ]));
        return new RedirectResponse(Url::fromUserInput($this->getDestination())
          ->toString());
      }
    }
    else {
      // Don't try to close $temp_file since it's FALSE at this point.
      \Drupal::messenger()->addError(t('Temporary file "%path" could not be opened for file upload', ['%path' => $temp_file_path]));
      return new RedirectResponse(Url::fromUserInput($this->getDestination())
        ->toString());
    }

    fclose($temp_file);

    // Stream the temporary file to the users browser.
    return new BinaryFileResponse($temp_file_path, 200, [
      'Content-disposition' => 'attachment; filename=' . $filename,
      'Content-type' => 'application/x-yaml',
    ]);
  }

  /**
   * Generator that yields each dependency of a config entity by scanning
   * recursively until it runs out of entities. Ignores duplicates.
   *
   * @param $entities
   * @param array $excluded_entity_type_ids
   * @param array $list
   * @param bool $recurse
   *
   * @return \Generator
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function buildPackageEntityList($entities, $excluded_entity_type_ids = [], &$list = [], $recurse = TRUE) {
    foreach ($entities as $entity) {
      // Re-calculate the usage for this entity.
      $this->usageUpdateManager->buildRequires($entity);

      // Get the Sync plugin for this entity.
      if ($plugin = $this->getPluginInstanceFromType($entity->getEntityType())) {
        // Don't yield if already sent or in the excluded list.
        $dependency_name = $entity->getConfigDependencyName();
        if (!isset($list[$dependency_name]) && !in_array($entity->getEntityTypeId(), $excluded_entity_type_ids)) {
          // Add this entity to the list so we don't yield something already sent.
          $list[$entity->getConfigDependencyName()] = TRUE;

          // And yield it.
          yield [
            'dependency_name' => $entity->getConfigDependencyName(),
            'type' => $entity->getEntityTypeId(),
            'entity' => $entity,
          ];

          // Loop through it's dependencies.
          /** @var SyncPluginInterface $plugin */
          foreach ($plugin->getDependencies($entity) as $key => $items) {
            foreach ($items as $item) {
              if (is_array($item)) {
                $id = NULL;
                $uuid = NULL;
                $type = NULL;

                if ($key == 'config') {
                  $type = $item['type'];
                  $id = $item['id'];
                }
                else {
                  if ($key == 'content') {
                    $type = $item['type'];
                    $uuid = $item['uuid'];
                  }
                }

                // Try and load the entity.
                try {
                  // Config entity by id.
                  $tentity = NULL;
                  if ($id) {
                    $tentity = $this->entityTypeManager->getStorage($type)
                      ->load($id);
                  }

                  if (!$tentity) {
                    // Content entity id uuid.
                    if (!$tentity = $this->entityRepository->loadEntityByUuid($type, $uuid)) {
                      continue;
                    }
                  }
                }
                catch (\Exception $e) {
                  continue;
                }

                if ($recurse) {
                  // Don't recurse te next entry if exporting a package entity.
                  yield from $this->buildPackageEntityList([$tentity], $excluded_entity_type_ids, $list, $entity instanceof Package ? FALSE : $recurse);
                }
              }
            }
          }
        }
      }
    }
  }


  /**
   * Wrapper function to get config print output multiple lines.
   *
   * @return bool
   *   TRUE if configuration set to print output multiple lines.
   */
  protected function isJsonOutputModeMultiline() {
    static $config = NULL;

    if (is_null($config)) {
      $config = $this->configFactory->get('cohesion.sync.settings')->get('json_output_multiline') ?? FALSE;
    }

    return $config;
  }

  /**
   * Do additional processing for the YAML data.
   *
   * Do this before finally printing into file.
   *
   * @param array $data
   *   Data being sent to YAML.
   *
   * @return array
   */
  protected function processYamlData(array $data) {
    // Return as is if no conversion required.
    if (!($this->isJsonOutputModeMultiline())) {
      return $data;
    }

    // For now we do this only for json_values and json_mapper.
    // @todo make the fields dynamic.
    foreach (['json_mapper', 'json_values'] as $field) {
      if (isset($data['export'][$field])) {
        $json = json_decode($data['export'][$field], TRUE);
        $data['export'][$field] = json_encode($json, JSON_PRETTY_PRINT);
      }
    }

    return $data;
  }

  /**
   * Get the YAML data with multiple lines in string data.
   *
   * Here the change is that we pass the additional flag
   * SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK to allow
   * printing string values in multiple lines.
   *
   * @param mixed $data
   *   The data to encode.
   *
   * @return string
   *   The encoded data.
   *
   * @see \Drupal\Component\Serialization\YamlSymfony::encode()
   */
  protected function yamlEncode(array $data) {
    try {
      // Set the indentation to 2 to match Drupal's coding standards.
      $yaml = new Dumper(2);
      return $yaml->dump($data, PHP_INT_MAX, 0, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE | SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
    }
    catch (\Exception $e) {
      throw new InvalidDataTypeException($e->getMessage(), $e->getCode(), $e);
    }
  }

}
