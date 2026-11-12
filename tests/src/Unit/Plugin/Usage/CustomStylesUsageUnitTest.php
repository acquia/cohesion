<?php

namespace Drupal\Tests\cohesion_custom_styles\Unit\Plugin\Usage;

use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\cohesion_custom_styles\Plugin\Usage\CustomStylesUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class CustomStylesUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new CustomStylesUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_custom_styles\Plugin\Usage\CustomStylesUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          'content' => 'coh-style-red',
        ]),
      ],
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_custom_style',
        'id' => 'id-green',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new CustomStyle([], 'cohesion_custom_style'));

    // Check the results.
    $this->assertEquals(count($entities), 2);
    $this->assertEquals($entities[0]['uuid'], 'uuid=id-green');
    $this->assertEquals($entities[1]['uuid'], 'uuid=.coh-style-red');
  }

}
