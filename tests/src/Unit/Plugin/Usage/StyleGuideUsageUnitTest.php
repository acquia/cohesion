<?php

namespace Drupal\Tests\cohesion_style_guide\Unit\Plugin\Usage;

use Drupal\cohesion_style_guide\Entity\StyleGuide;
use Drupal\cohesion_style_guide\Plugin\Usage\StyleGuideUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class StyleGuideUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new StyleGuideUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_style_guide\Plugin\Usage\StyleGuideUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {
    // Test.
    $entities = $this->unit->scanForInstancesOfThisType([
      [
        'type' => 'json_string',
        'value' => '{token: "some prefix [style-guide:styleguideuid:fielduid] some suffix"}',
        'decoded' => [],
      ],
    ], new StyleGuide([], 'cohesion_style_guide'));

    $this->assertEquals(count($entities), 1);

    // Test.
    $entities = $this->unit->scanForInstancesOfThisType([
      [
        'type' => 'json_string',
        'value' => '{token: "some prefix [style-guide:broken] some suffix"}',
        'decoded' => [],
      ],
    ], new StyleGuide([], 'cohesion_style_guide'));

    $this->assertEquals(count($entities), 0);

    // Test.
    $entities = $this->unit->scanForInstancesOfThisType([
      [
        'type' => 'json_string',
        'value' => '{token: "some prefix [style-guide] some suffix"}',
        'decoded' => [],
      ],
    ], new StyleGuide([], 'cohesion_style_guide'));

    $this->assertEquals(count($entities), 0);

  }

}
