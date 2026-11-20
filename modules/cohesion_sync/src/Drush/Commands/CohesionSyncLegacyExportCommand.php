<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Drupal\cohesion_sync\Drush\CommandHelpers;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to export legacy format Site Studio packages to sync.
 */
final class CohesionSyncLegacyExportCommand extends DrushCommands {

  const NAME = 'sync:export';

  /**
   * CohesionSyncLegacyExportCommand constructor.
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
   * Export DX8 packages to sync.
   *
   * @command sync:export
   * @aliases sync-export
   * @option filename-prefix The export filename prefix that will output a file like: [prefix]-package.yml_
   * @validate-module-enabled cohesion_sync
   */
  #[CLI\Command(name: self::NAME, aliases: ['sync-export'])]
  #[CLI\Option(name: 'filename-prefix', description: 'The export filename prefix that will output a file like: [prefix]-package.yml_')]
  #[CLI\ValidateModulesEnabled(modules: ['cohesion_sync'])]
  public function legacyExport(array $options = ['filename-prefix' => NULL]): int {
    $this->io()->warning('You are using a deprecated package export command which will be removed in a future version of Site Studio. For more information refer to documentation at https://docs.acquia.com/drupal-starter-kits/add-ons/site-studio/deprecating-legacy-package-system');

    try {
      if ($result = $this->commandHelpers->exportAll($options['filename-prefix'])) {
        $this->io()->writeln($result);
        return self::EXIT_SUCCESS;
      }

      $this->io()->writeln((string) t('Unable to export Site Studio packages. Check the logs for more information.'));
    }
    catch (\Exception $e) {
      $this->io()->error($e->getMessage());
    }

    return self::EXIT_FAILURE;
  }

}
