<?php

/**
 * Implements hook_drush_command().
 */
function cohesion_sync_drush_command() {
  return [
    'sync-export' => [
      'description' => 'Export Cohesion packages to sync.',
      'options' => [
        'filename-prefix' => 'The export filename prefix that will output a file like: [prefix]-package.yml_',
      ],
      'drupal dependencies' => ['cohesion_sync'],
    ],
    'sync-import' => [
      'description' => 'Import Cohesion packages from sync.',
      'options' => [
        'overwrite-all' => 'Overwrite existing entities when differences detected',
        'keep-all' => 'Keep any existsing entities when differences detected',
        'path' => 'Specify a local or remote path to a *.package.yml file',
      ],
      'drupal dependencies' => ['cohesion_sync'],
    ],
  ];
}

/**
 * Call back function drush_cohesion_sync_sync_export()
 */
function drush_cohesion_sync_sync_export() {
  // Get options.
  $filename_prefix = drush_get_option('filename-prefix');

  // Attempt the import.
  try {
    if ($result = \Drupal::service('cohesion_sync.drush_helpers')->exportAll($filename_prefix)) {
      drush_print($result);
    }
    else {
      drush_set_error('Cohesion', 'Unable to export Cohesion packages. Check the dblog for more information.');
    }
  }
  catch (\Exception $e) {
    drush_set_error('Cohesion', $e->getMessage());
  }
}

/**
 * Call back function drush_cohesion_sync_sync_import()
 */
function drush_cohesion_sync_sync_import() {
  // Get options.
  $overwrite_all = drush_get_option('overwrite-all');
  $keep_all = drush_get_option('keep-all');
  $path = drush_get_option('path');

  // One must be set.
  try {
    if ($overwrite_all || $keep_all) {
      $results = \Drupal::service('cohesion_sync.drush_helpers')
        ->import($overwrite_all == 1, $keep_all == 1, $path);

      drush_print($results);
    }
    // None of the options set.
    else {
      drush_set_error('Cohesion', 'You must use one of the following options: --overwrite-all OR --keep-all');
    }
  }
  catch (\Exception $e) {
    drush_set_error('Cohesion', $e->getMessage());
  }

}
