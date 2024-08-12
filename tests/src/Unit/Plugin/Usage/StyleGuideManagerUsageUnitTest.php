<?php

namespace Drupal\Tests\cohesion_style_guide\Unit\Plugin\Usage;

use Drupal\cohesion_style_guide\Entity\StyleGuideManager;
use Drupal\cohesion_style_guide\Plugin\Usage\StyleGuideManagerUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class StyleGuideManagerUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new StyleGuideManagerUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock,
      $this->theme_handler_mock);
  }

  /**
   * @covers \Drupal\cohesion_style_guide\Plugin\Usage\StyleGuideManagerUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'coh_style_guide_manager',
        'style_guide_uuid' => '0000-0000-0000-0000',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new StyleGuideManager([], 'cohesion_style_guide_manager'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], '0000-0000-0000-0000');
  }

}
