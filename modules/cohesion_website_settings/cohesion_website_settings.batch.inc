<?php

/**
 * Copy temporary stylesheets to live.
 * Copy temporary templates to live.
 * Notify the user.
 *
 * @param $success
 * @param $results
 * @param $operations
 */
function entity_rebuild_finished_callback($success, $results, $operations) {
  if (!is_array($results)) {
    $results = [];
  }

  // The 'success' parameter means no fatal PHP errors were detected. All
  // other error management should be handled using 'results'.
  if ($success && !isset($results['error'])) {

    $running_dx8_batch = &drupal_static('running_dx8_batch');
    $running_dx8_batch = TRUE;

    \Drupal::service('cohesion.local_files_manager')->tempToLive();
    \Drupal::service('cohesion.local_files_manager')->moveTemporaryTemplateToLive();
    \Drupal::messenger()->addMessage(t('Cohesion entities have successfully been rebuilt. Your styles are now live.'));
  }
  else {
    \Drupal::messenger()->addError($results['error']);
  }
}

