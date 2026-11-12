<?php

namespace Drupal\Tests\cohesion_website_settings\Unit\Plugin\Usage;

use Drupal\cohesion_website_settings\Entity\Color;
use Drupal\cohesion_website_settings\Plugin\Usage\ColorPaletteUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class ColorPaletteUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ColorPaletteUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_website_settings\Plugin\Usage\ColorPaletteUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          'content' => '$coh-color-r3-D $coh-color||eg@|-characters',
          'content2' => 'starttext$coh-color-green endtext',
        ]),
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new Color([], 'cohesion_color'));

    // Check the results.
    $this->assertEquals(count($entities), 2);
    $this->assertEquals($entities[0]['uuid'], 'uuid=r3-D');
    $this->assertEquals($entities[1]['uuid'], 'uuid=green');
  }

}
