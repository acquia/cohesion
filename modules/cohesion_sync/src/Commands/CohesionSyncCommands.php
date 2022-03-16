<?php

namespace Drupal\cohesion_sync\Commands;

use Drupal\cohesion_sync\Drush\CommandHelpers;
use Drush\Commands\DrushCommands;
use Consolidation\AnnotatedCommand\CommandResult;

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
   * Drush sync command helper.
   *
   * @var \Drupal\cohesion_sync\Drush\CommandHelpers
   */
  private $commandHelpers;

  /**
   * CohesionSyncCommands constructor.
   *
   * @param \Drupal\cohesion_sync\Drush\CommandHelpers $command_helpers
   */
  public function __construct(CommandHelpers $command_helpers) {
    $this->commandHelpers = $command_helpers;
  }

  /**
   * Export DX8 packages to sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases,
   *   config, etc.
   *
   * @option filename-prefix
   *   The export filename prefix that will output a file
   *   like: [prefix]-package.yml_
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sync:export
   * @aliases sync-export
   */
  public function export(array $options = ['filename-prefix' => NULL]) {

    $filename_prefix = $options['filename-prefix'];

    try {
      if ($result = $this->commandHelpers->exportAll($filename_prefix)) {
        $this->say($result);
        return CommandResult::exitCode(self::EXIT_SUCCESS);
      }
      else {
        $this->say(t('Site Studio', 'Unable to export Site Studio packages. Check the dblog for more information.'));
      }
    }
    catch (\Exception $e) {
      $this->yell($e->getMessage());
    }
    return CommandResult::exitCode(self::EXIT_FAILURE);
  }

  /**
   * Import DX8 packages from sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli, aliases,
   *   config, etc.
   *
   * @option overwrite-all
   *   Overwrite existing entities when differences detected
   * @option keep-all
   *   Keep any existing entities when differences detected
   * @option path
   *   Specify a local or remote path to a *.package.yml file
   * @option force
   *   Force importing entities even if this will break content
   * @option no-rebuild
   *   Prevent rebuilding imported entities
   * @option no-maintenance
   *   Optionally skip maintenance mode step
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sync:import
   * @aliases sync-import
   */
  public function import(array $options = [
    'overwrite-all' => NULL,
    'keep-all' => NULL,
    'path' => NULL,
    'force' => NULL,
    'no-rebuild' => NULL,
    'no-maintenance' => NULL,
  ]) {
    // Get options.
    $overwrite_all = $options['overwrite-all'];
    $keep_all = $options['keep-all'];
    $path = $options['path'];
    $force = $options['force'];
    $no_rebuild = $options['no-rebuild'];
    $no_maintenance = $options['no-maintenance'];

    // One must be set.
    try {
      if ($overwrite_all || $keep_all) {
        $operations = $this->commandHelpers->import($overwrite_all == 1, $keep_all == 1, $path, $force == 1, $no_rebuild, $no_maintenance == 1);

        $batch = [
          'title' => t('Validating configuration.'),
          'operations' => $operations,
          'progressive' => FALSE,
        ];

        batch_set($batch);
        $result = drush_backend_batch_process();
        return is_array($result) && isset(array_shift($result)['error']) ? CommandResult::exitCode(self::EXIT_FAILURE) : CommandResult::exitCode(self::EXIT_SUCCESS);
      }
      // None of the options set.
      else {
        $this->say(t('You must use one of the following options: --overwrite-all OR --keep-all'));
      }
    }
    catch (\Exception $e) {
      $this->yell($e->getMessage(), 200, 'red');
    }

    return CommandResult::exitCode(self::EXIT_FAILURE);
  }

}
