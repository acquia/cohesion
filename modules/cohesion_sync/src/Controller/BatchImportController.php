<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\cohesion_style_guide\Entity\StyleGuideManager;
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

    /** @var StyleGuideManager[] $style_guide_managers */
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
      \Drupal::messenger()->addMessage(t('Finished with an error.'));
    }
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   */
  public static function batchReportCallback($path, &$context) {
    if (!isset($context['results']['error'])) {
      $context['message'] = t('Imported @count items from package: @path', ['@count' => count($context['results']), '@path' => $path]);
    }
  }

  /**
   * @param $success
   * @param $results
   * @param $operations
   *
   */
  public static function batchDrushFinishedCallback($path, &$context) {
    if (!isset($context['results']['error'])) {
      $context['message'] = t('Imported @count items from package: @path', ['@count' => count($context['results']), '@path' => $path]);
    }
    else {
      \Drupal::messenger()->addMessage(t('Finished with an error.'));
    }
  }
}
