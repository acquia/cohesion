<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Drupal\cohesion_sync\Services\PackageImportHandler;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Drush\Drush;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to import multiple packages based on a package manifest.
 */
final class CohesionSyncMultiImportCommand extends DrushCommands {

  const NAME = 'sitestudio:package:multi-import';

  /**
   * CohesionSyncMultiImportCommand constructor.
   *
   * @param \Drupal\cohesion_sync\Services\PackageImportHandler $packageImportHandler
   *   Package Import handler service.
   */
  public function __construct(protected PackageImportHandler $packageImportHandler) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('cohesion_sync.package_import_handler'));
  }

  /**
   * Import multiple packages based on package manifest.
   *
   * @command sitestudio:package:multi-import
   * @aliases cohesion:package:multi-import
   * @option path Path to package manifest.
   * @validate-module-enabled cohesion_sync
   * @usage sitestudio:package:multi-import --path=/path/to/manifest.yml Import packages listed in the manifest.
   */
  #[CLI\Command(name: self::NAME, aliases: ['cohesion:package:multi-import'])]
  #[CLI\Option(name: 'path', description: 'Path to package manifest.')]
  #[CLI\ValidateModulesEnabled(modules: ['cohesion_sync'])]
  #[CLI\Usage(name: self::NAME . ' --path=/path/to/manifest.yml', description: 'Import packages listed in the manifest.')]
  public function multiImport(array $options = ['path' => NULL]): int {
    $path = $options['path'];
    if (!$path) {
      $this->io()->error('Please provide the path to the package manifest using the --path option.');
      return self::EXIT_FAILURE;
    }

    if (!str_starts_with($path, '/')) {
      // Path is relative to where the cmd was called.
      $path = Drush::config()->cwd() . '/' . $path;
    }

    try {
      $result = $this->packageImportHandler->importPackagesFromPath($path);
    }
    catch (\Exception $exception) {
      $this->io()->error($exception->getMessage());
      return self::EXIT_FAILURE;
    }
    drush_backend_batch_process();

    return is_array($result) && isset(array_shift($result)['error']) ? self::EXIT_FAILURE : self::EXIT_SUCCESS;
  }

}
