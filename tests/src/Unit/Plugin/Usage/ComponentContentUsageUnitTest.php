<?php

namespace Drupal\Tests\cohesion_elements\Unit\Plugin\Usage;

use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_elements\Plugin\Usage\ComponentContentUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class ComponentContentUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ComponentContentUsage(
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
            'componentContentId' => 'cc_mycomponentcontent',
          ],
        ],
      ],
    ];

    // This should be ComponentContent but works regardless.
    $entities = $this->unit->scanForInstancesOfThisType($fixture, new Component([], 'component_content'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'uuid=mycomponentcontent');
  }

}
