<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\EntityViewModeUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the core view mode entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MockViewMode extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

/**
 * @group Cohesion
 */
class EntityViewModeUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new EntityViewModeUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock
    );
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\EntityViewModeUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'drupal_view_mode',
        'uuid' => 'f646b8e6-6606-4cb4-ad3e-5f9e9ba88e7e',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MockViewMode([], 'drupal_view_mode'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'f646b8e6-6606-4cb4-ad3e-5f9e9ba88e7e');
  }

}
