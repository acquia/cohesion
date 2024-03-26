<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\ContentEntityFieldUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the core field entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MockField extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

/**
 * @group Cohesion
 */
class ContentEntityFieldUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ContentEntityFieldUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock
    );
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\ContentEntityFieldUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'drupal_field',
        'entity_type' => 'field_config',
        'uuid' => '9c55f394-382b-4003-850c-97a003ede078',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MockField([], 'field_config'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], '9c55f394-382b-4003-850c-97a003ede078');
  }

}
