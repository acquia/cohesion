<?php

namespace Drupal\Tests\cohesion_website_settings\Unit\Plugin\Usage;

use Drupal\cohesion_website_settings\Entity\SCSSVariable;
use Drupal\cohesion_website_settings\Plugin\Usage\SCSSVariableUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class SCSSVariableUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new SCSSVariableUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_website_settings\Plugin\Usage\SCSSVariableUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          'content' => '$coh-color-r3-D $coh-font-r3_D $SOME_var1able    f;g;flkg $s0me-variable $some_^^^variable',
          'content2' => 'starttext$coh-color-green endtext',
        ]),
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new SCSSVariable([], 'cohesion_scss_variable'));

    // Check the results.
    $this->assertEquals(count($entities), 3);
    $this->assertEquals($entities[0]['uuid'], 'uuid=SOME_var1able');
    $this->assertEquals($entities[1]['uuid'], 'uuid=s0me-variable');
    $this->assertEquals($entities[2]['uuid'], 'uuid=some_');
  }

}
