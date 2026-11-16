<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\EntityFormDisplayUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the core form display entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MockFormDisplay extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

/**
 * @group Cohesion
 */
class EntityFormDisplayUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new EntityFormDisplayUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock
    );
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\EntityFormDisplayUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'drupal_form_display',
        'uuid' => '64bc0b1e-5779-48eb-bdc1-ab61f4588271',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MockFormDisplay([], 'drupal_form_display'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], '64bc0b1e-5779-48eb-bdc1-ab61f4588271');
  }

}
