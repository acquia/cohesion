<?php

namespace Drupal\Tests\cohesion_elements\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion_elements\Plugin\Usage\ComponentContentUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * Mock for the component content entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class ComponentContentMock extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

class ComponentContentUsageUnitTestMock extends ComponentContentUsage {

  public function loadComponentContent($v) {
    return 'c6074a02-73e9-4f76-ae29-522ff35a70c4';
  }

}

/**
 * @group Cohesion
 */
class ComponentContentUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ComponentContentUsageUnitTestMock(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_elements\Plugin\Usage\ComponentContentUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'value' => NULL,
        'decoded' => [
          'canvas' => [
            'componentContentId' => 'cc_c6074a02-73e9-4f76-ae29-522ff35a70c4',
          ],
        ],
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new ComponentContentMock([], 'component_content'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'c6074a02-73e9-4f76-ae29-522ff35a70c4');
  }

}
