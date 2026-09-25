<?php

namespace Drupal\cohesion_sync\Drush\Commands;

use Drush\Commands\DrushCommands;
use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides drush commands for sync packages.
 *
 * @package Drupal\cohesion_sync\Drush\Commands
 */
class CohesionSyncPackageCommands extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CohesionSyncPackageCommands constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct();
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *
   * @return static
   */
  public static function create(ContainerInterface $container): self {
    $commandHandler = new static(
      $container->get('entity_type.manager')
    );

    return $commandHandler;
  }

  public function getPackages() {
    /** @var \Drupal\cohesion_sync\Entity\Package[] $packages */
    $packages = $this->entityTypeManager
      ->getStorage('cohesion_sync_package')
      ->loadMultiple();

    return $packages;

  }

  /**
   * List sync packages.
   *
   * @validate-module-enabled cohesion_sync
   *
   * @command sitestudio:package:list
   */
  public function listPackage() {

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

    return CommandResult::exitCode(self::EXIT_SUCCESS);
  }

}
