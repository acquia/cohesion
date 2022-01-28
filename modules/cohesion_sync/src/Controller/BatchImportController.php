<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion_style_guide\Entity\StyleGuideManager;
use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Controller\ControllerBase;
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
      $context['message'] = t('Pre processing:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
    }
  }

  /**
   * Set a batch for entities that needs to be rebuilt if style guide or style guide manager
   * as been imported
   *
   * @param $context
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function inUseRebuild(&$context) {
    if(!\Drupal::moduleHandler()->moduleExists('cohesion_style_guide')) {
      return;
    }
    $style_guide_managers = [];
    $style_guides_uuids = [];
    // extract all style guide manager and style guide that have been
    // processed during import
    foreach ($context['results'] as $result) {
      $result_entity = explode(':', $result);
      // $result[1] == entity uuid - $result_entity[0] == entity type.
      if(is_array($result_entity) && count($result_entity) == 2 && isset($result_entity[0])) {
        if($result_entity[0] == 'cohesion_style_guide_manager') {
          $style_guide_managers[] = $result_entity[1];
        }elseif ($result_entity[0] == 'cohesion_style_guide') {
          $style_guides_uuids[] = $result_entity[1];
        }
      }
    }

    // Extract each style guide for each style guide manager
    $style_guide_managers_ids = \Drupal::entityTypeManager()->getStorage('cohesion_style_guide_manager')->getQuery()
      ->condition('status', TRUE)
      ->condition('uuid', $style_guide_managers, 'IN')
      ->execute();

    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuideManager[] $style_guide_managers */
    $style_guide_managers = StyleGuideManager::loadMultiple($style_guide_managers_ids);
    foreach ($style_guide_managers as $style_guide_manager) {
      $decoded_json = $style_guide_manager->getDecodedJsonValues(TRUE);
      if (property_exists($decoded_json, 'model') && is_object($decoded_json->model)) {
        foreach ($decoded_json->model as $style_guide_uuid => $style_guide_values) {
          $style_guides_uuids[] = $style_guide_uuid;
        }
      }
    }

    if(!empty($style_guides_uuids)) {
      $in_use_list = \Drupal::database()->select('coh_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.requires_uuid', $style_guides_uuids, 'IN')
        ->execute()
        ->fetchAllKeyed();

      // remove any entity that would already have been resaved by the import
      foreach ($in_use_list as $uuid => $entity_type) {
        if(in_array("{$entity_type}:{$uuid}", $context['results'])) {
          unset($in_use_list[$uuid]);
        }
      }

      \Drupal::service('cohesion.rebuild_inuse_batch')->run($in_use_list);
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
    $package_manager = \Drupal::service('cohesion_sync.packager');
    $config_storage = \Drupal::service('config.storage');
    $source_storage = new StorageReplaceDataWrapper($config_storage);

    if($entries = \Drupal::service('cohesion_sync.packager')->getExportsByUUID($uri, $uuids)) {
      foreach ($entries as $entry) {
        $package_manager->replaceData($source_storage, $entry, $action_data);
        $context['message'] = t('Importing:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
        $context['results'][] = $entry['type'] . ':' . $entry['export']['uuid'];
      }

      $config_importer = $package_manager->getConfigImporter($source_storage);
      $config_importer->import();
    }

  }

  /**
   * @param $entry
   * @param $context
   */
  public static function batchPostAction($uri, $uuid, $action_data, &$context) {
    if (isset($context['results']['error'])) {
      return;
    }

    $package_manager = \Drupal::service('cohesion_sync.packager');
    if($entry = $package_manager->getExportByUUID($uri, $uuid)) {
      $entry['export'] = $package_manager->matchUUIDS($action_data, $entry['export']);
      $package_manager->postApplyPackageEntry($entry);
      $context['message'] = t('Building:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
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
      return new RedirectResponse('/admin/cohesion/sync/import/report');
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
    if (!isset($context['results']['error'])) {
      $context['message'] = t('Imported @count items from package: @path', ['@count' => count($context['results']), '@path' => $path]);
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
      $no_maintenance ?: \Drupal::service('state')->set('system.maintenance_mode', FALSE);
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
    // Skip if there is validation errors
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
        if($entry = $package_manager->getExportByUUID($file_uri, $entry_uuid)) {
          $context['message'] = t('Validating: @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry_uuid]);
          $action_data = $package_manager->validatePackageEntry($entry);
          $broken_entities = [];
          // Check content will not be lost if entity exists
          if ($entry['type'] == 'cohesion_component' || $entry['type'] == 'cohesion_style_guide') {
            /** @var \Drupal\cohesion_elements\Entity\Component $entity */
            $entity = $entity_repository->loadEntityByUuid($entry['type'], $entry['export']['uuid']);
            if($entity) {
              $entities = $entity->checkContentIntegrity($entry['export']['json_values']);
              if(!empty($entities)) {
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
      // If the validation fails set the data to false so it can be process by the form
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

    // Check if no any entity has been marked as broken and will loose data after importing
    if (isset($sync_data['broken_entities']) && $overwrite && !$force) {
      $broken_entities = $sync_data['broken_entities'];
      $error_messages = [];
      $drupal_translate = \Drupal::translation();
      foreach ($broken_entities as $broken_entity) {
        /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity */
        $entity = $broken_entity['entity'];
        $error_messages[] = "\n";
        $error_messages[] = $drupal_translate->translate('Cannot import @entity_type \'@label\' (id: @id). This entity is missing populated fields. If you proceed, content in these fields will be lost.', ['@entity_type' => $entity['type'], '@label' => $entity['label'], '@id' => $entity['id']]);
        $error_messages[] = $drupal_translate->formatPlural(count($broken_entity['entities']), '1 entity affected:', '@count entities affected:');
        foreach ($broken_entity['entities'] as $broken) {
          $error_messages[] = $drupal_translate->translate('@entity_type \'@label\' (id: @id)', ['@entity_type' => $broken['type'], '@label' => $broken['label'], '@id' => $broken['id']]);
        }
      }

      if (!empty($error_messages)) {
        $error_messages[] = "\n" . $drupal_translate->translate('You can choose to ignore and proceed with the import by adding `--force` to your command');
        $context['results']['error'] = implode("\n", $error_messages);
        \Drupal::service('messenger')->addError(implode("\n", $error_messages));
        // If the validation fails set the data to false so it can be process by the form
        $temp_store->set($store_key, FALSE);
        return;
      }
    }

    if(isset($sync_data['action_data'])) {
      $action_data = $sync_data['action_data'];
      // Set the status of the action items denpending on the drush command options.
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

      /** @var \Drupal\cohesion_sync\PackagerManager $package_manager */
      $package_manager = \Drupal::service('cohesion_sync.packager');
      $operations = $package_manager->applyBatchYamlPackageStream($path, $action_data, $no_rebuild);

      $operations[] = [
        '\Drupal\cohesion_sync\Controller\BatchImportController::batchDrushFinishedCallback',
        [$path, $no_maintenance]
      ];

      $batch = [
        'title' => t('Importing configuration.'),
        'operations' => $operations,
        'progressive' => FALSE,
      ];

      batch_set($batch);
    }
  }

}
