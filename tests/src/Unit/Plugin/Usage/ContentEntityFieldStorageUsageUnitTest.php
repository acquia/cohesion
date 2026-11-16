<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\ContentEntityFieldStorageUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the core field entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MockFieldStorage extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

/**
 * @group Cohesion
 */
class ContentEntityFieldStorageUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ContentEntityFieldStorageUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\ContentEntityFieldStorageUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'field_storage_config',
        'id' => 'c277fbdb-2f2b-4e55-a2df-c119fd9b90f5',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MockFieldStorage([], 'field_storage_config'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'c277fbdb-2f2b-4e55-a2df-c119fd9b90f5');
  }

}
