<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Drupal\cohesion_sync\Drush\CommandHelpers;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to import legacy format Site Studio packages from sync.
 */
final class CohesionSyncLegacyImportCommand extends DrushCommands {

  const NAME = 'sync:import';

  /**
   * CohesionSyncLegacyImportCommand constructor.
   *
   * @param \Drupal\cohesion_sync\Drush\CommandHelpers $commandHelpers
   *   Drush sync command helper.
   */
  public function __construct(protected CommandHelpers $commandHelpers) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('cohesion_sync.drush_helpers'));
  }

  /**
   * Import DX8 packages from sync.
   *
   * @command sync:import
   * @aliases sync-import
   * @option overwrite-all Overwrite existing entities when differences detected
   * @option keep-all Keep any existing entities when differences detected
   * @option path Specify a local or remote path to a *.package.yml file
   * @option force Force importing entities even if this will break content
   * @option no-rebuild Prevent rebuilding imported entities
   * @option no-maintenance Optionally skip maintenance mode step
   * @validate-module-enabled cohesion_sync
   */
  #[CLI\Command(name: self::NAME, aliases: ['sync-import'])]
  #[CLI\Option(name: 'overwrite-all', description: 'Overwrite existing entities when differences detected')]
  #[CLI\Option(name: 'keep-all', description: 'Keep any existing entities when differences detected')]
  #[CLI\Option(name: 'path', description: 'Specify a local or remote path to a *.package.yml file')]
  #[CLI\Option(name: 'force', description: 'Force importing entities even if this will break content')]
  #[CLI\Option(name: 'no-rebuild', description: 'Prevent rebuilding imported entities')]
  #[CLI\Option(name: 'no-maintenance', description: 'Optionally skip maintenance mode step')]
  #[CLI\ValidateModulesEnabled(modules: ['cohesion_sync'])]
  public function legacyImport(
    array $options = [
      'overwrite-all' => FALSE,
      'keep-all' => FALSE,
      'path' => NULL,
      'force' => FALSE,
      'no-rebuild' => FALSE,
      'no-maintenance' => FALSE,
    ],
  ): int {
    $this->io()->warning('You are using a deprecated package import command which will be removed in a future version of Site Studio. For more information refer to documentation at https://docs.acquia.com/drupal-starter-kits/add-ons/site-studio/deprecating-legacy-package-system');

    $overwrite_all = $options['overwrite-all'];
    $keep_all = $options['keep-all'];

    // One must be set.
    try {
      if ($overwrite_all || $keep_all) {
        $operations = $this->commandHelpers->import(
          $overwrite_all,
          $keep_all,
          $options['path'],
          $options['force'],
          $options['no-rebuild'],
          $options['no-maintenance']
        );

        $batch = [
          'title' => t('Validating configuration.'),
          'operations' => $operations,
          'progressive' => FALSE,
        ];

        batch_set($batch);
        $result = drush_backend_batch_process();
        return is_array($result) && isset(array_shift($result)['error']) ? self::EXIT_FAILURE : self::EXIT_SUCCESS;
      }

      $this->io()->writeln((string) t('You must use one of the following options: --overwrite-all OR --keep-all'));
    }
    catch (\Exception $e) {
      $this->io()->error($e->getMessage());
    }

    return self::EXIT_FAILURE;
  }

}
