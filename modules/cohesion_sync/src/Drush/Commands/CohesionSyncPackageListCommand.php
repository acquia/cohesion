<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Drush command to list sync packages.
 *
 * @package Drupal\cohesion_sync\Drush\Commands
 */
class CohesionSyncPackageListCommand extends DrushCommands {

  const NAME = 'sitestudio:package:list';

  /**
   * CohesionSyncPackageListCommand constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(protected EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): self {
    return new self($container->get('entity_type.manager'));
  }

  /**
   * Gets all sync packages.
   *
   * @return \Drupal\cohesion_sync\Entity\Package[]
   *   The sync packages.
   */
  public function getPackages() {
    return $this->entityTypeManager
      ->getStorage('cohesion_sync_package')
      ->loadMultiple();
  }

  /**
   * List sync packages.
   *
   * @command sitestudio:package:list
   * @validate-module-enabled cohesion_sync
   * @usage sitestudio:package:list List sync packages.
   */
  #[CLI\Command(name: self::NAME)]
  #[CLI\ValidateModulesEnabled(modules: ['cohesion_sync'])]
  #[CLI\Usage(name: self::NAME, description: 'List sync packages.')]
  public function listPackages(): int {
    $packages = $this->getPackages();

    if ($packages) {
      $this->output()->writeln('Available Site Studio packages:');
      $this->output()->writeln('');

      foreach ($packages as $package) {
        $this->output()->writeln($package->get('label') . ' - id: ' . $package->get('id'));
      }
    }
    // No sync packages created.
    else {
      $this->output()->writeln('No Site Studio packages available');
    }

    return self::EXIT_SUCCESS;
  }

}
