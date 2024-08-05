<?php

namespace Drupal\Tests\cohesion_website_settings\Unit\Plugin\Usage;

use Drupal\cohesion_website_settings\Entity\FontStack;
use Drupal\cohesion_website_settings\Plugin\Usage\FontStackUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class FontStackUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new FontStackUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_website_settings\Plugin\Usage\FontStackUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          'content' => '$coh-font-r3_D $coh-font-||eg@|-characters',
          'content2' => 'starttext$coh-font-green endtext',
        ]),
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new FontStack([], 'cohesion_font_stack'));

    // Check the results.
    $this->assertEquals(count($entities), 2);
    $this->assertEquals($entities[0]['uuid'], 'uuid=r3_D');
    $this->assertEquals($entities[1]['uuid'], 'uuid=green');
  }

}
