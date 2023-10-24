<?php

namespace Drupal\Tests\cohesion\Unit\Commands;

use Drupal\cohesion_sync\Commands\CohesionSyncPackageCommands;
use Drupal\cohesion_sync\Entity\Package;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;


class CohesionSyncPackageCommandsUnitTestMock extends CohesionSyncPackageCommands {

  private $fixture_package = ['id' => 'pack_another_package', 'label' => 'another package'];

  public function getPackages() {

    $package = new Package($this->fixture_package, 'cohesion_sync_package');

    return [$package->id() => $package];
  }

}
/**
 * @group Cohesion
 */
class CohesionSyncPackageCommandsUnitTest extends UnitTestCase {

  protected $unit;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entityTypeManager = $this->getMockBuilder('Drupal\Core\Entity\EntityTypeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    // Create a dummy container.
    $this->container = new ContainerBuilder();
    $this->container->set('entity_type.manager', $this->entityTypeManager);

    \Drupal::setContainer($this->container);

    // Init the plugin.
    $this->unit = new CohesionSyncPackageCommandsUnitTestMock(
      $this->entityTypeManager
    );
  }

  /**
   * @covers \Drupal\cohesion_sync\Commands\CohesionSyncPackageCommands::listPackage
   */
  public function testListPackages() {

    $command_exit_code_info = $this->unit->listPackage();

    // check the exit code and data.
    $this->assertEquals($command_exit_code_info->getExitCode(), 0);
  }

}

