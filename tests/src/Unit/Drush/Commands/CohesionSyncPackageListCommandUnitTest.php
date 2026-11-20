<?php

namespace Drupal\Tests\cohesion\Unit\Drush\Commands;

use Drupal\cohesion_sync\Drush\Commands\CohesionSyncPackageListCommand;
use Drupal\cohesion_sync\Entity\Package;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use PHPUnit\Framework\Attributes\CoversMethod;
use PHPUnit\Framework\Attributes\Group;


#[CoversMethod(\Drupal\cohesion_sync\Drush\Commands\CohesionSyncPackageCommands::class, 'listPackage')]
class CohesionSyncPackageListCommandUnitTestMock extends CohesionSyncPackageListCommand {

  private $fixture_package = ['id' => 'pack_another_package', 'label' => 'another package'];

  public function getPackages() {

    $package = new Package($this->fixture_package, 'cohesion_sync_package');

    return [$package->id() => $package];
  }

}
/**
 * @group Cohesion
 */
#[CoversMethod(CohesionSyncPackageCommands::class, 'listPackage')]
#[Group('Cohesion')]
class CohesionSyncPackageListCommandUnitTest extends UnitTestCase {

  protected $unit;

  /**
   * @var \PHPUnit\Framework\MockObject\MockObject|\Drupal\Core\Entity\EntityTypeManagerInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  private $entityTypeManager;

  /**
   * @var \Drupal\Core\DependencyInjection\ContainerBuilder
   */
  private $container;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->entityTypeManager = $this->getMockBuilder('Drupal\Core\Entity\EntityTypeManagerInterface')
      ->disableOriginalConstructor()
      ->getMock();

    // Create a dummy container.
    $this->container = new ContainerBuilder();
    $this->container->set('entity_type.manager', $this->entityTypeManager);

    \Drupal::setContainer($this->container);

    // Init the plugin.
    $this->unit = new CohesionSyncPackageListCommandUnitTestMock(
      $this->entityTypeManager
    );
  }

  /**
   * @covers \Drupal\cohesion_sync\Drush\Commands\CohesionSyncPackageListCommand::listPackages
   */
  public function testListPackages(): void {

    $output = new BufferedOutput();
    $this->unit->setInput(new ArrayInput([]));
    $this->unit->setOutput($output);

    $this->assertEquals(0, $this->unit->listPackages());
    $this->assertStringContainsString('another package - id: pack_another_package', $output->fetch());
  }

}

