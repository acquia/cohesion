<?php

namespace Drupal\Tests\cohesion_templates\Unit\Plugin\Usage;

use Drupal\cohesion_templates\Entity\MasterTemplates;
use Drupal\cohesion_templates\Plugin\Usage\MasterTemplatesUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class MasterTemplatesUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new MasterTemplatesUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * Return a specific master template.
   *
   * @covers \Drupal\cohesion_templates\Plugin\Usage\MasterTemplatesUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_master_templates',
        'id' => 'id-green',
      ],
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_master_templates',
        'id' => '__none__',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MasterTemplates([], 'cohesion_master_templates'));

    // Check the results.
    $this->assertEquals(count($entities), 2);
    $this->assertEquals($entities[0]['uuid'], 'uuid=id-green');
    $this->assertEquals($entities[1]['uuid'], 'uuid=default');
  }

}
