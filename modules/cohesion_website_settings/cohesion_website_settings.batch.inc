<?php

/**
 * @file
 */

use Drupal\cohesion\Event\SiteStudioEvents;
use Drupal\cohesion\Event\PostRebuildEvent;

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

    \Drupal::messenger()->addMessage(t('Site Studio entities have successfully been rebuilt. Your styles are now live.'));
  }
  else {
    \Drupal::messenger()->addError(t('Site Studio rebuild failed to complete. @error',
      ['@error' => $results['error']]
    ));

  }

  // Setup an event to be dispatched post rebuild.
  $post_event = new PostRebuildEvent($results, $success);
  \Drupal::service('event_dispatcher')->dispatch($post_event, SiteStudioEvents::POST_REBUILD);
  \Drupal::logger('cohesion')->info('Rebuild operation complete.');
}

/**
 * @param $verbose
 * @param $context
 */
function entity_rebuild_temp_to_live($verbose = FALSE, &$context = []) {
  if (!isset($context['results']['error'])) {
    $time_start = microtime(TRUE);

    $running_dx8_batch = &drupal_static('running_dx8_batch');
    $running_dx8_batch = TRUE;

    try {
      \Drupal::service('cohesion.local_files_manager')->tempToLive();
      \Drupal::service('cohesion.template_storage')->commit();
    }
    catch (\Throwable $e) {
      $context['results']['error'] = $e->getMessage();
    }

    $time_end = microtime(TRUE);
    // Batch message.
    if ($verbose) {
      $context['message'] = t('Moving all templates and stylesheets into place. (@time seconds, @memory MB (total) @memory_peak MB (peak), start at: @time_start, end at: @time_end)', [
        '@time' => number_format((float) microtime(TRUE) - $time_start, 2, '.', ''),
        '@memory' => number_format(memory_get_usage() / 1024768, 2, '.', ','),
        '@memory_peak' => number_format(memory_get_peak_usage() / 1024768, 2, '.', ','),
        '@time_start' => date("H:i:s", $time_start),
        '@time_end' => date("H:i:s", $time_end),
      ]);
    }
    else {
      $context['message'] = t('Moving all templates and stylesheets into place.');
    }

  }
}

/**
 * @param $verbose
 * @param array $context
 */
function batch_drupal_flush_all_caches($verbose = FALSE, &$context = []) {
  if (!isset($context['results']['error'])) {
    $time_start = microtime(TRUE);

    drupal_flush_all_caches();

    $time_end = microtime(TRUE);
    if ($verbose) {
      $context['message'] = t('Flushing caches. (@time seconds, @memory MB (total) @memory_peak MB (peak), start at: @time_start, end at: @time_end)', [
        '@time' => number_format((float) microtime(TRUE) - $time_start, 2, '.', ''),
        '@memory' => number_format(memory_get_usage() / 1024768, 2, '.', ','),
        '@memory_peak' => number_format(memory_get_peak_usage() / 1024768, 2, '.', ','),
        '@time_start' => date("H:i:s", $time_start),
        '@time_end' => date("H:i:s", $time_end),
      ]);
    }
    else {
      $context['message'] = t('Flushing caches.');
    }
  }
}
