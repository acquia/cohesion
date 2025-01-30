<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\cohesion_sync\Services\PackageImportHandler;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
    PackageImportHandler $packageImportHandler,
  ) {
    parent::__construct();
    $this->packageImportHandler = $packageImportHandler;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container): self {
    return new self(
      $container->get('cohesion_sync.package_import_handler')
    );
  }

  /**
   * Import multiple packages based on package manifest.
   *
   * @param array $options
   *   An associative array of options whose values come from cli.
   *
   * @option path
   *   Path to package manifest.
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sitestudio:package:multi-import
   * @aliases cohesion:package:multi-import
   */
  public function siteStudioMultiImport(
    array $options = [
      'path' => NULL,
    ],
  ) {

    $path = $options['path'];

    if (strpos($path, '/') !== 0) {
      // Path is relative to where the cmd was called.
      $path = $this->getConfig()->cwd() . '/' . $path;
    }

    try {
      $result = $this->packageImportHandler->importPackagesFromPath($path);
    }
    catch (\Exception $exception) {
      $this->warn($exception->getMessage(), 40, 'red');
      return CommandResult::exitCode(self::EXIT_FAILURE);
    }
    drush_backend_batch_process();

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
