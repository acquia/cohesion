<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion_sync\Config\CohesionFileStorage;
use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
use Drupal\cohesion_website_settings\Controller\WebsiteSettingsController;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\StorageComparerInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Installer\InstallerKernel;
use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Sync batch import controller.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class BatchImportController extends ControllerBase {

  /**
   * @param $uri
   * @param $uuids
   * @param $context
   */
  public static function batchAction($uri, $uuids, &$context) {
    if (isset($context['results']['error'])) {
      return;
    }
    // Apply the entities to the site.
    $entries = \Drupal::service('cohesion_sync.packager')->getExportsByUUID($uri, $uuids);
    foreach ($entries as $entry) {
      \Drupal::service('cohesion_sync.packager')->applyPackageEntry($entry);
      $context['message'] = t('Pre processing:  @type - @uuid', [
        '@type' => $entry['type'],
        '@uuid' => $entry['export']['uuid'],
      ]);
    }
  }

  /**
   * @param $uri
   * @param $uuids
   * @param $action_data
   * @param $context
   */
  public static function batchConfigImport($uri, $uuids, $action_data, &$context) {
    if (isset($context['results']['error'])) {
      return;
    }
    // Make sure API send() function just returns without sending anything to
    // the API.
    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');
    $cohesion_sync_lock = TRUE;
    $package_manager = \Drupal::service('cohesion_sync.packager');
    $config_storage = \Drupal::service('config.storage');
    $source_storage = new StorageReplaceDataWrapper($config_storage);

    if ($entries = \Drupal::service('cohesion_sync.packager')->getExportsByUUID($uri, $uuids)) {
      foreach ($entries as $entry) {
        $package_manager->replaceData($source_storage, $entry, $action_data);
        $context['message'] = t('Importing:  @type - @uuid', [
          '@type' => $entry['type'],
          '@uuid' => $entry['export']['uuid'],
        ]);
        $context['results'][] = $entry['type'] . ':' . $entry['export']['uuid'];
      }

      $config_importer = $package_manager->getConfigImporter($source_storage);
      $config_importer->import();
    }

  }

  /**
   * Run rebuild depending on package entities being imported
   *
   * @param $entry
   * @param $context
   */
  public static function batchPostAction($entities_need_rebuild, $needs_complete_rebuild, &$context) {

    $options = [
      'verbose' => FALSE,
      'no-cache-clear' => FALSE,
    ];

    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');
    $cohesion_sync_lock = FALSE;
    if ($needs_complete_rebuild) {
      $batch = WebsiteSettingsController::batch(TRUE, $options['verbose'], $options['no-cache-clear']);
      batch_set($batch);
    } else {
      \Drupal::service('cohesion.rebuild_inuse_batch')->run($entities_need_rebuild);
    }
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   */
  public static function batchFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      \Drupal::messenger()->addMessage(t('The import succeeded. @count tasks completed.', ['@count' => count($results)]));
      return new RedirectResponse(Url::fromRoute('cohesion_sync.import_report')->toString());
    }
    else {
      \Drupal::messenger()->addError(t('Finished with an error.'));
    }
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   */
  public static function batchFinishedValidationCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      \Drupal::messenger()->addMessage(t('Validation successful. Review actions and changes below and import you package.'));
    }
    else {
      \Drupal::messenger()->addError(t('Validation failed.'));
    }
  }

  /**
   * Last operation sync import
   *
   * @param $path
   *
   */
  public static function batchDrushFinishedCallback($path, $no_maintenance, &$context) {
    $no_maintenance ?: \Drupal::service('state')->set('system.maintenance_mode', FALSE);
    $no_maintenance ?: \Drupal::messenger()->addMessage(t('Maintenance mode disabled'));
    if (!isset($context['results']['error'])) {
      $context['message'] = t('Imported @count items from package: @path', [
        '@count' => count($context['results']),
        '@path' => $path,
      ]);
    }
    else {
      \Drupal::messenger()->addError(t('Finished with an error.'));
    }
  }

  /**
   * Last operation validation sync import
   *
   */
  public static function batchDrushValidationFinishedCallback($no_maintenance, &$context) {
    if (!isset($context['results']['error'])) {
      $context['message'] = t('Validation done');
    }
    else {
      \Drupal::messenger()->addError(t('Finished with an error.'));
    }
  }

  /**
   * Validate the package file is readable.
   *
   * @param $file_uri
   * @param $context
   */
  public static function batchValidatePackage($file_uri, &$context) {
    if (@ !fopen($file_uri, 'r')) {
      $error = "Cannot access {$file_uri}";
      $context['results']['error'] = $error;
      \Drupal::service('messenger')->addError($error);
    }
  }

  /**
   * Validate an entry of a sync package
   *
   * @see \Drupal\cohesion_sync\Form\ImportFileForm
   *
   * @param $file_uri - the uri to the sync package
   * @param $entry_uuids - the entity uuids in the package needing to be validated
   * @param $store_key - the shared private sitestudio key to store the data
   * @param $context - batch context
   */
  public static function batchValidateEntry($file_uri, $entry_uuids, $store_key, &$context) {
    // Skip if there is validation errors.
    if ($context['results'] && $context['results']['error']) {
      return;
    }
    /** @var \Drupal\cohesion_sync\PackagerManager $package_manager */
    $package_manager = \Drupal::service('cohesion_sync.packager');
    $temp_store = \Drupal::service('tempstore.shared')->get('sitestudio');
    /** @var \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository */
    $entity_repository = \Drupal::service('entity.repository');
    try {
      foreach ($entry_uuids as $entry_uuid) {
        if ($entry = $package_manager->getExportByUUID($file_uri, $entry_uuid)) {
          $context['message'] = t('Validating: @type - @uuid', [
            '@type' => $entry['type'],
            '@uuid' => $entry_uuid,
          ]);
          $action_data = $package_manager->validatePackageEntry($entry);
          $broken_entities = [];
          // Check content will not be lost if entity exists.
          if ($entry['type'] == 'cohesion_component' || $entry['type'] == 'cohesion_style_guide') {
            /** @var \Drupal\cohesion_elements\Entity\Component $entity */
            $entity = $entity_repository->loadEntityByUuid($entry['type'], $entry['export']['uuid']);
            if ($entity) {
              $entities = $entity->checkContentIntegrity($entry['export']['json_values']);
              if (!empty($entities)) {
                $broken_entities = [
                  'in_use_url' => $entity->toUrl('in-use'),
                  'entity' => [
                    'label' => $entity->label(),
                    'type' => $entity->getEntityType()->getLabel(),
                    'id' => $entity->id(),
                  ],
                ];

                foreach ($entities as $broken_entity) {
                  $broken_entities['entities'][] = [
                    'label' => $broken_entity->label(),
                    'type' => $broken_entity->getEntityType()->getLabel(),
                    'id' => $broken_entity->id(),
                  ];
                }

              }
            }
          }

          $sync_data = $temp_store->get($store_key);
          if($sync_data == NULL) {
            $sync_data = [
              'action_data' => [],
              'broken_entities' => [],
            ];
          }
          // Validate the stream.
          $sync_data['action_data'][$entry_uuid] = $action_data;
          if (!empty($broken_entities)) {
            $sync_data['broken_entities'][$entry_uuid] = $broken_entities;
          }
          $temp_store->set($store_key, $sync_data);
        }
      }
    }catch (\Exception $e) {
      $context['results']['error'] = $e->getMessage();
      \Drupal::service('messenger')->addError($e->getMessage());
      // If the validation fails set the data to false so it can be process by
      // the form.
      $temp_store->set($store_key, FALSE);
    }
  }

  /**
   * Batch operation that sets another batch to import a packages if validation
   * of that package has been successful - for drush use
   *
   * @param $path
   * @param $store_key
   * @param $overwrite
   * @param $keep
   * @param $force
   * @param $no_rebuild
   * @param $context
   *
   * @throws \Exception
   */
  public static function setImportBatch($path, $store_key, $overwrite, $keep, $force, $no_rebuild, $no_maintenance, &$context) {

    $temp_store = \Drupal::service('tempstore.shared')->get('sitestudio');
    $sync_data = $temp_store->get($store_key);

    // Check if no any entity has been marked as broken and will loose data
    // after importing.
    if (isset($sync_data['broken_entities']) && $overwrite && !$force) {
      $broken_entities = $sync_data['broken_entities'];
      $error_messages = [];
      $drupal_translate = \Drupal::translation();
      foreach ($broken_entities as $broken_entity) {
        /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity */
        $entity = $broken_entity['entity'];
        $error_messages[] = "\n";
        $error_messages[] = $drupal_translate->translate('Cannot import @entity_type \'@label\' (id: @id). This entity is missing populated fields. If you proceed, content in these fields will be lost.',
          [
            '@entity_type' => $entity['type'],
            '@label' => $entity['label'],
            '@id' => $entity['id'],
          ]
        );
        $error_messages[] = $drupal_translate->formatPlural(count($broken_entity['entities']), '1 entity affected:', '@count entities affected:');
        foreach ($broken_entity['entities'] as $broken) {
          $error_messages[] = $drupal_translate->translate('@entity_type \'@label\' (id: @id)', [
            '@entity_type' => $broken['type'],
            '@label' => $broken['label'],
            '@id' => $broken['id'],
          ]);
        }
      }

      if (!empty($error_messages)) {
        $error_messages[] = "\n" . $drupal_translate->translate('You can choose to ignore and proceed with the import by adding `--force` to your command');
        $context['results']['error'] = implode("\n", $error_messages);
        \Drupal::service('messenger')->addError(implode("\n", $error_messages));
        // If the validation fails set the data to false so it can be process
        // by the form.
        $temp_store->set($store_key, FALSE);
        return;
      }
    }

    if (isset($sync_data['action_data'])) {
      $action_data = $sync_data['action_data'];
      // Set the status of the action items denpending on the drush command
      // options.
      foreach ($action_data as $uuid => $action) {
        if ($action['entry_action_state'] == ENTRY_EXISTING_ASK) {
          if ($overwrite) {
            $action_data[$uuid]['entry_action_state'] = ENTRY_EXISTING_OVERWRITTEN;
          }
          if ($keep) {
            $action_data[$uuid]['entry_action_state'] = ENTRY_EXISTING_IGNORED;
          }
        }
      }

      // For a full import, set the site to maintenance mode.
      $no_maintenance ?: \Drupal::state()->set('system.maintenance_mode', TRUE);
      $no_maintenance ?: \Drupal::messenger()->addMessage(t('Maintenance mode enabled'));

      /** @var \Drupal\cohesion_sync\PackagerManager $package_manager */
      $package_manager = \Drupal::service('cohesion_sync.packager');
      $operations = $package_manager->applyBatchYamlPackageStream($path, $action_data, $no_rebuild);

      $operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::batchDrushFinishedCallback',
        [$path, $no_maintenance],
      ];

      $batch = [
        'title' => t('Importing configuration.'),
        'operations' => $operations,
        'progressive' => FALSE,
      ];

      batch_set($batch);
    }
  }

  public static function fileImport(CohesionFileStorage $file_storage, string $path, &$context) {
    $files = $file_storage->getFiles();
    $file_sync_event = new SiteStudioSyncFilesEvent($files, $path);
    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface $event_dispatcher */
    $event_dispatcher = \Drupal::service('event_dispatcher');
    $event_dispatcher->dispatch($file_sync_event, $file_sync_event::IMPORT);
  }

  /**
   * Handles rebuild during package import.
   *
   * @param \Drupal\Core\Config\StorageComparerInterface $storage_comparer
   *   Storage Comparer service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function handleRebuilds(StorageComparerInterface $storage_comparer) {
    $sync_import = \Drupal::service('cohesion_sync.sync_import');
    $change_list = $sync_import->buildChangeList($storage_comparer->getChangelist());

    if ($sync_import->needsCompleteRebuild($change_list)) {
      $batch = WebsiteSettingsController::batch(TRUE);
      batch_set($batch);
    }
    else {
      $rebuild_list = $sync_import->findAffectedEntities($change_list, $storage_comparer);
      if (!empty($rebuild_list)) {
        \Drupal::service('cohesion.rebuild_inuse_batch')->run($rebuild_list);
      }
    }
  }

  /**
   * Rebuild the in-use table for each entity that is using a deleted config
   *
   * @param \Drupal\Core\Config\StorageComparerInterface $storage_comparer
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function handleInuse(array $recreates) {

    /** @var \Drupal\cohesion\UsageUpdateManager $usage_update_manager */
    $usage_update_manager = \Drupal::service('cohesion_usage.update_manager');
    /** @var \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager */
    $entity_type_manager = \Drupal::service('entity_type.manager');
    foreach ($recreates as $old_uuid => $recreate) {
      // Get the list of entities using recreated entity.
      $in_use_entities = $usage_update_manager->getInUseEntitiesListByUuid($old_uuid);
      foreach ($in_use_entities as $uuid => $entity_type) {
        // If the entity itself has been recreated, use new UUID.
        if (array_key_exists($uuid, $recreates)) {
          $uuid = $recreates[$uuid]['new'];
        }
        $entities = $entity_type_manager
          ->getStorage($entity_type)
          ->loadByProperties(['uuid' => $uuid]);
        $entity = reset($entities);
        $usage_update_manager->buildRequires($entity);
      }
    }

  }

  /**
   * Finish batch.
   *
   * This function is a static function to avoid serializing the ConfigSync
   * object unnecessarily.
   *
   * @param bool $success
   *   Indicate that the batch API tasks were all completed successfully.
   * @param array $results
   *   An array of all the results that were updated in update_do_one().
   * @param array $operations
   *   A list of the operations that had not been completed by the batch API.
   */
  public static function finish($success, $results, $operations) {
    $messenger = \Drupal::messenger();
    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');

    if ($success) {
      if (!empty($results['errors'])) {
        $logger = \Drupal::logger('config_sync');
        foreach ($results['errors'] as $error) {
          $messenger->addError($error);
          $logger->error($error);
        }
        $messenger->addWarning(t('Package configuration was imported with errors.'));
      }
      elseif (!InstallerKernel::installationAttempted()) {
        // Display a success message when not installing Drupal.
        $messenger->addStatus(t('Package configuration was imported successfully.'));
        if (is_array($cohesion_file_sync_messages) && !empty($cohesion_file_sync_messages)) {
          $messenger->addStatus(t('Package files were processed successfully. In total :new new files imported and :updated existing files updated.', [
            ':new' => $cohesion_file_sync_messages['new_files'],
            ':updated' => $cohesion_file_sync_messages['updated_files'],
          ]));
        }
      }
    }
    else {
      // An error occurred.
      // $operations contains the operations that remained unprocessed.
      $error_operation = reset($operations);
      $message = t('An error occurred while processing %error_operation with arguments: @arguments',
        [
          '%error_operation' => $error_operation[0],
          '@arguments' => print_r($error_operation[1], TRUE),
        ]);
      $messenger->addError($message);
    }
  }

}
