<?php

namespace Drupal\cohesion_sync\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion_sync\Services\PackageImportHandler;
use Drush\Commands\DrushCommands;

/**
 * Cohesion Package Import Command.
 */
class CohesionSyncMultiImportCommand extends DrushCommands {

  /**
   * Package Import handler service.
   *
   * @var \Drupal\cohesion_sync\Services\PackageImportHandler
   */
  protected $packageImportHandler;

  /**
   * CohesionSyncImportCommand constructor.
   *
   * @param \Drupal\cohesion_sync\Services\PackageImportHandler $packageImportHandler
   *   Package Import handler service.
   */
  public function __construct(
    PackageImportHandler $packageImportHandler
  ) {
    parent::__construct();
    $this->packageImportHandler = $packageImportHandler;
  }

  /**
   * Import Cohesion packages from sync.
   *
   * @param array $options
   *   An associative array of options whose values come from cli.
   *
   * @option path
   *   Specify path to a directory with package files.
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sitestudio:package:multi-import
   * @aliases cohesion:package:multi-import
   */
  public function siteStudioMultiImport(array $options = [
    'path' => NULL,
  ]) {

    try {
      $result = $this->packageImportHandler->importPackagesFromPath($options['path']);
    }
    catch (\Exception $exception) {
      $this->warn($exception->getMessage(), 40, 'red');
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }

    drush_backend_batch_process();

    $cohesion_file_sync_messages = &drupal_static('suppress_drupal_messages');
    if (isset($cohesion_file_sync_messages['new_files']) || isset($cohesion_file_sync_messages['updated_files'])) {
      $this->yell(sprintf('Imported %s new and updated %s existing non-config files.', $cohesion_file_sync_messages['new_files'], $cohesion_file_sync_messages['updated_files']));
    }
    else {
      $this->yell('No non-config files were imported or updated during sync.');
    }

    return is_array($result) && isset(array_shift($result)['error']) ? CommandResult::exitCode(self::EXIT_FAILURE) : CommandResult::exitCode(self::EXIT_SUCCESS);
  }

  /**
   * Wrapper for warning user of errors.
   *
   * @param $text
   *   Error text.
   * @param $length
   *   Length of output line.
   * @param $color
   *   Color - defaults to red.
   *
   * @return void
   */
  protected function warn($text, $length = 40, $color = 'red') {
    $this->yell($text, $length, $color);
  }

}
