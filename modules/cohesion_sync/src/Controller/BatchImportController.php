<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\config\StorageReplaceDataWrapper;
use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class BatchImportController.
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
    // Apply the entities to the site.
    $entries = $entry = \Drupal::service('cohesion_sync.packager')->getExportsByUUID($uri, $uuids);
    foreach ($entries as $entry) {
      \Drupal::service('cohesion_sync.packager')->applyPackageEntry($entry);
      $context['message'] = t('Pre processing:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
    }

    $context['results'][] = TRUE;
  }

  /**
   * @param $uri
   * @param $uuids
   * @param $action_data
   * @param $context
   */
  public static function batchConfigImport($uri, $uuids, $action_data, &$context) {

    $package_manager = \Drupal::service('cohesion_sync.packager');
    $config_storage = \Drupal::service('config.storage');
    $source_storage = new StorageReplaceDataWrapper($config_storage);

    if($entries = \Drupal::service('cohesion_sync.packager')->getExportsByUUID($uri, $uuids)) {
      foreach ($entries as $entry) {
        $package_manager->replaceData($source_storage, $entry, $action_data);
        $context['message'] = t('Importing:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
      }

      $config_importer = $package_manager->getConfigImporter($source_storage);
      $config_importer->import();
    }

    $context['results'][] = TRUE;
  }

  /**
   * @param $entry
   * @param $context
   */
  public static function batchPostAction($uri, $uuid, $action_data, &$context) {

    $package_manager = \Drupal::service('cohesion_sync.packager');
    if($entry = $package_manager->getExportByUUID($uri, $uuid)) {
      $entry['export'] = $package_manager->matchUUIDS($action_data, $entry['export']);
      $package_manager->postApplyPackageEntry($entry);
      $context['message'] = t('Building:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
      $context['results'][] = TRUE;
    }
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public static function batchFinishedCallback($success, $results, $operations) {
    // The 'success' parameter means no fatal PHP errors were detected. All
    // other error management should be handled using 'results'.
    if ($success) {
      \Drupal::messenger()->addMessage(t('The import succeeded. @count tasks completed.', ['@count' => count($results)]));
      return new RedirectResponse('/admin/cohesion/sync/import/report');
    }
    else {
      \Drupal::messenger()->addMessage(t('Finished with an error.'));
    }
  }

  /**
   * Clear the results session data.
   *
   * @param $entry
   * @param $context
   */
  public static function batchCompleteAction($action_data, &$context) {
    $context['message'] = t('Building the report.');
    $context['results'][] = TRUE;

    \Drupal::service('tempstore.private')->get('sync_report')->set('report', $action_data);
  }
}
