<?php

namespace Drupal\cohesion_sync\Commands;

use Drush\Commands\DrushCommands;
use Consolidation\AnnotatedCommand\CommandResult;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides drush commands for sync packages.
 *
 * @package Drupal\cohesion_sync\Commands
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
