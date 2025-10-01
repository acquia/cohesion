<?php

namespace Drupal\cohesion_sync;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Entity\Package;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Config\StorageComparer;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
   * @var \Drupal\Core\File\FileSystemInterface
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
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger_factory
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   */
  public function __construct(EntityRepository $entityRepository, EntityTypeManagerInterface $entityTypeManager, SyncPluginManager $sync_plugin_manager, UsageUpdateManager $usage_update_manager, FileSystemInterface $file_system, LoggerChannelFactoryInterface $logger_factory, StorageInterface $config_storage) {
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->syncPluginManager = $sync_plugin_manager;
    $this->usageUpdateManager = $usage_update_manager;
    $this->fileSystem = $file_system;
    $this->logger = $logger_factory->get('cohesion_sync');
    $this->configStorage = $config_storage;
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
      // Create a reflection class to see if this entity implements in the
      // interface in the annotation.
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
          if ($entry_data['export']['uuid'] == $uuid) {
            $this->flattenJsonEntry($entry_data);
            $entry = $entry_data;
          }
          $yaml = $line;
        }
        // Building up an array entry.
        else {
          $yaml .= $line;
        }
      }

      if ($entry == NULL) {
        $entry_data = Yaml::decode($yaml)[0];
        if ($entry_data['export']['uuid'] == $uuid) {
          $this->flattenJsonEntry($entry_data);
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
          if (in_array($entry_data['export']['uuid'], $uuids)) {
            $this->flattenJsonEntry($entry_data);
            $entries[] = $entry_data;
          }
          $yaml = $line;
        }
        // Building up an array entry.
        else {
          $yaml .= $line;
        }
      }

      if ($yaml != "-\n") {
        $entry_data = Yaml::decode($yaml)[0];
        if (in_array($entry_data['export']['uuid'], $uuids)) {
          $entries[] = $entry_data;
        }
      }

      fclose($handle);

      return $entries;
    }
  }

  /**
   * Converts jsons from package entry to one line jsons for storage.
   *
   * @param array $entry
   *   The entry for the package.
   */
  private function flattenJsonEntry(array &$entry) {
    $is_multiline = Settings::get('site_studio_package_multiline', FALSE);
    if ($is_multiline) {
      // Flatten any json_values.
      if (isset($entry['export']['json_values']) && is_string($entry['export']['json_values'])) {
        $entry['export']['json_values'] = CohesionFileStorage::minifyJson($entry['export']['json_values']);
      }

      // Flatten any json_mapper.
      if (isset($entry['export']['json_mapper']) && is_string($entry['export']['json_mapper'])) {
        $entry['export']['json_mapper'] = CohesionFileStorage::minifyJson($entry['export']['json_mapper']);
      }

      // Flatten any json_values.
      if ($entry['type'] == 'cohesion_sync_package' && isset($entry['export']['settings']) && is_string($entry['export']['settings'])) {
        $entry['export']['settings'] = CohesionFileStorage::minifyJson($entry['export']['settings']);
      }
    }
  }

  /**
   * Validate a package list via the plugin and return the actions.
   *
   * @param array $entry
   *   Entity entry.
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

    return $plugin->getActionData($entry['export'], $action_state, $entry['type']);
  }

  /**
   * Sets the batch for validating a package.
   *
   * @param string $file_uri
   *   File URI.
   * @param string $store_key
   *   Store key.
   *
   * @return array
   *   Operations for the batch.
   */
  public function validatePackageBatch(string $file_uri, string $store_key) {

    $operations = [];

    $operations[] = [
      '\Drupal\cohesion_sync\Controller\BatchImportController::batchValidatePackage',
      [$file_uri],
    ];

    $entity_uuids_to_validate = [];
    $this->parseYaml($file_uri, function ($entry) use (&$operations, $file_uri, $store_key, &$entity_uuids_to_validate) {
      $entity_uuids_to_validate[] = $entry['export']['uuid'];
    });

    // Set a limit for the batch process or use a configured override of X.
    $batch_limit = Settings::get('sync_max_entity', 10);

    for ($i = 0; $i < count($entity_uuids_to_validate); $i += $batch_limit) {
      $ids = array_slice($entity_uuids_to_validate, $i, $batch_limit);
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

    // Operations for the batch process.
    $operations = [];
    // Entities that need to be imported. Can be a mix of config (site studio
    // and others) and Files.
    $uuids = [];
    // Entities that need to be rebuilt.
    $entities_need_rebuild = [];
    // Does the sync requires an entire rebuild because base unit settings or
    // responsive grid has been changed.
    $needs_complete_rebuild = FALSE;

    foreach ($action_data as $uuid => $action) {
      if (in_array($action['entry_action_state'], [
        ENTRY_NEW_IMPORTED,
        ENTRY_EXISTING_OVERWRITTEN,
      ])) {
        // Does this sync require a full rebuild.
        $entity_type_need_full_rebuild = [
          'base_unit_settings',
          'responsive_grid_settings',
        ];
        if ($action['entity_type'] == 'cohesion_website_settings' && in_array($action['id'], $entity_type_need_full_rebuild)) {
          $needs_complete_rebuild = TRUE;
        }

        $config_prefix = 'cohesion_';
        if (!$needs_complete_rebuild && $action['is_config'] && substr($action['entity_type'], 0, strlen($config_prefix)) === $config_prefix) {
          // If the entity already exisits under a different UUID the UUID
          // currently in DB will be used.
          $uuid_in_use = $action['replace_uuid'] ?? $action['entry_uuid'];
          // If a cohesion config, add it to the list of entities that neeeds
          // to be rebuilt.
          $entities_need_rebuild[$uuid_in_use] = $action['entity_type'];

          // Entity types that might be used in other entity that will need to
          // be rebuild as well as the entity. Ex: a scss variable that has a
          // change in value, we need to rebuild every entity where this scss
          // variable is used.
          $entity_type_with_dependency = [
            'cohesion_scss_variable',
            'cohesion_style_guide',
            'cohesion_color',
            'cohesion_font_stack',
          ];
          if (in_array($action['entity_type'], $entity_type_with_dependency)) {

            $usage = \Drupal::database()->select('coh_usage', 'c1')
              ->fields('c1', ['source_uuid', 'source_type'])
              ->condition('c1.requires_uuid', $uuid_in_use, '=')
              ->execute()
              ->fetchAllKeyed();

            $entities_need_rebuild = array_merge($entities_need_rebuild, $usage);
          }

        }

        // Store all entities that need to be imported.
        $uuids[] = $uuid;
      }
    }

    // Set a limit for the batch process or use a configured override of X.
    $batch_limit = Settings::get('sync_max_entity', 10);

    $uuids_batch = [];
    for ($i = 0; $i < count($uuids); $i += $batch_limit) {
      $uuids_batch[] = array_slice($uuids, $i, $batch_limit);
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
      $operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::batchPostAction',
        [$entities_need_rebuild, $needs_complete_rebuild],
      ];
    }

    // Apllies only if there has been some imports.
    if (!empty($uuids_batch)) {
      $operations[] = [
        'cohesion_elements_get_elements_style_process_batch',
        [],
      ];

      // Generate content template entities for any new entity type / bundle /
      // view mode.
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
   *   Source Storage.
   * @param array $entry
   *   Entity entry.
   * @param array $action_data
   *   Action data.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function replaceData(StorageReplaceDataWrapper &$source_storage, array $entry, array $action_data) {
    $type_definition = $this->entityTypeManager->getDefinition($entry['type']);
    // Only applies to config entities (ie: excludes files)
    if ($type_definition instanceof ConfigEntityType) {

      $config_id = $entry['export'][$type_definition->getKey('id')];
      if ($type_definition->id() == 'cohesion_custom_style') {
        $custom_style_ids = \Drupal::entityQuery('cohesion_custom_style')
          ->accessCheck(TRUE)
          ->condition('class_name', $entry['export']['class_name'])
          ->execute();

        if ($custom_style_ids && !in_array($config_id, $custom_style_ids)) {
          $config_id = end($custom_style_ids);
        }
      }

      $config_name = $type_definition->getConfigPrefix() . '.' . $config_id;
      $entry['export'] = $this->matchUUIDS($action_data, $entry['export']);
      $source_storage->replaceData($config_name, $entry['export']);
    }
  }

  /**
   * Fetches Config Importer.
   *
   * @param $source_storage
   *   Source Storage.
   *
   * @return \Drupal\cohesion_sync\SyncConfigImporter
   */
  public function getConfigImporter($source_storage) {
    $storage_comparer = new StorageComparer(
      $source_storage,
      $this->configStorage
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
      \Drupal::service('extension.list.module'),
      \Drupal::service('extension.list.theme')
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
      $to_import_type = [ENTRY_NEW_IMPORTED, ENTRY_EXISTING_OVERWRITTEN];
      if (in_array($data['entry_action_state'], $to_import_type) && isset($data['replace_uuid'])) {
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
            $is_multiline = Settings::get('site_studio_package_multiline', FALSE);
            if ($is_multiline) {
              // Json values multiline.
              if (isset($item['export']['json_values']) && is_string($item['export']['json_values'])) {
                $item['export']['json_values'] = CohesionFileStorage::prettyPrintJson($item['export']['json_values']);
              }

              if (isset($item['export']['json_mapper']) && is_string($item['export']['json_mapper'])) {
                $item['export']['json_mapper'] = CohesionFileStorage::prettyPrintJson($item['export']['json_mapper']);
              }

              // Package settings multiline.
              if ($item['type'] == 'cohesion_sync_package' && isset($item['export']['settings']) && is_string($item['export']['settings'])) {
                $item['export']['settings'] = CohesionFileStorage::prettyPrintJson($item['export']['settings']);
              }
              yield SymfonyYaml::dump([$item], PHP_INT_MAX, 2, SymfonyYaml::DUMP_EXCEPTION_ON_INVALID_TYPE + SymfonyYaml::DUMP_MULTI_LINE_LITERAL_BLOCK);
            }
            else {
              yield Yaml::encode([$item]);
            }
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

      // Get the Sync plugin for this entity.
      if ($plugin = $this->getPluginInstanceFromType($entity->getEntityType())) {
        // Don't yield if already sent or in the excluded list.
        $dependency_name = $entity->getConfigDependencyName();
        if (!isset($list[$dependency_name]) && !in_array($entity->getEntityTypeId(), $excluded_entity_type_ids)) {
          // Add this entity to the list so we don't yield something already
          // sent.
          $list[$entity->getConfigDependencyName()] = TRUE;
          // And yield it.
          yield [
            'dependency_name' => $entity->getConfigDependencyName(),
            'type' => $entity->getEntityTypeId(),
            'entity' => $entity,
          ];

          $dependencies = $plugin->getDependencies($entity);

          // Group uuids to be processed by entity type
          // to use loadMultiple instead of loading by UUID one by one
          // for better performance
          $typed_uuids = [];
          foreach ($dependencies as $items) {
            foreach ($items as $dependency) {
              if(is_array($dependency)){
                $typed_uuids[$dependency['type']][] = $dependency['uuid'];
              }
            }
          }

          // Loop through the results and add them to the dependencies.
          foreach ($typed_uuids as $type => $uuids) {
            $entity_type = $this->entityTypeManager->getDefinition($type);
            $ids = $this->entityTypeManager->getStorage($type)->getQuery()
              ->accessCheck(TRUE)
              ->condition($entity_type->getKey('uuid'), $uuids, 'IN')
              ->execute();

            $entities = $this->entityTypeManager->getStorage($type)->loadMultiple($ids);

            foreach ($entities as $dependency_entity) {
              if ($recurse) {
                // Don't recurse te next entry if exporting a package entity.
                yield from $this->buildPackageEntityList([$dependency_entity], $excluded_entity_type_ids, $list, $entity instanceof Package ? FALSE : $recurse);
              }
            }
          }
        }
      }
    }
  }

}
