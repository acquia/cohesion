<?php

/**
 * @file
 */

/**
 * Notify the user.
 *
 * @param $success
 * @param $results
 */
function element_usage_batch_finished($success, $results) {
  if (!is_array($results)) {
    $results = [];
  }

  // The 'success' parameter means no fatal PHP errors were detected. All
  // other error management should be handled using 'results'.
  if ($success && !isset($results['error'])) {

    \Drupal::messenger()->addMessage(t('Site Studio element usage report has successfully been regenerated.'));
  }
  else {
    \Drupal::messenger()->addError(t('Site Studio element usage report failed to complete. @error',
      ['@error' => $results['error']]
    ));

  }
}

/**
 * Build the coh_element_usage table.
 */
function _rebuild_element_usage() {
  \Drupal::service('cohesion_element_usage.manager')->buildRequires('batch');
}
