<?php

namespace Drupal\cohesion_sync\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Class BatchImportController.
 *
 * @package Drupal\cohesion_sync\Controller
 */
class BatchImportController extends ControllerBase {

  /**
   * @param $entry
   * @param $context
   */
  public static function batchAction($entry, &$context) {
    \Drupal::service('cohesion_sync.packager')->applyPackageEntry($entry);
    $context['message'] = t('Importing:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
    $context['results'][] = TRUE;
  }

  /**
   * @param $entry
   * @param $context
   */
  public static function batchPostAction($entry, &$context) {

    // Let this throw errors if something goes wrong.
    \Drupal::service('cohesion_sync.packager')->postApplyPackageEntry($entry);

    $context['message'] = t('Building:  @type - @uuid', ['@type' => $entry['type'], '@uuid' => $entry['export']['uuid']]);
    $context['results'][] = TRUE;
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
