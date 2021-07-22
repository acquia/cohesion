<?php

namespace Drupal\cohesion_sync\Commands;

use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class CohesionSyncCommands extends DrushCommands {

  /**
   * Export DX8 packages to sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @option filename-prefix
   *   The export filename prefix that will output a file like: [prefix]-package.yml_
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sync:export
   * @aliases sync-export
   */
  public function export(array $options = ['filename-prefix' => NULL]) {

    $filename_prefix = $options['filename-prefix'];

    try {
      if ($result = \Drupal::service('cohesion_sync.drush_helpers')->exportAll($filename_prefix)) {
        $this->say($result);
      }
      else {
        $this->say(t('Site Studio', 'Unable to export Site Studio packages. Check the dblog for more information.'));
      }
    }
    catch (\Exception $e) {
      $this->yell($e->getMessage());
    }
  }

  /**
   * Import DX8 packages from sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases, config, etc.
   *
   * @option overwrite-all
   *   Overwrite existing entities when differences detected
   * @option keep-all
   *   Keep any existing entities when differences detected
   * @option path
   *   Specify a local or remote path to a *.package.yml file
   * @option force
   *   Force importing entities even if this will break content
   * @validate-module-enabled cohesion_sync
   *
   * @command sync:import
   * @aliases sync-import
   */
  public function import(array $options = ['overwrite-all' => NULL, 'keep-all' => NULL, 'path' => NULL, 'force' => NULL]) {
    // Get options.
    $overwrite_all = $options['overwrite-all'];
    $keep_all = $options['keep-all'];
    $path = $options['path'];
    $force = $options['force'];

    // One must be set.
    try {
      if ($overwrite_all || $keep_all) {
        $results = \Drupal::service('cohesion_sync.drush_helpers')->import($overwrite_all == 1, $keep_all == 1, $path, $force == 1);

        $this->say($results);
      }
      // None of the options set.
      else {
        $this->say(t('You must use one of the following options: --overwrite-all OR --keep-all'));
      }
    }
    catch (\Exception $e) {
      $this->yell($e->getMessage(), 200, 'red');
    }
  }

}
