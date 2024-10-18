<?php

namespace Drupal\Tests\cohesion_website_settings\Unit\Plugin\Usage;

use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\cohesion_website_settings\Plugin\Usage\IconLibraryUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class IconLibraryUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new IconLibraryUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_website_settings\Plugin\Usage\IconLibraryUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'decoded' => [
          "contentIcon" => [
            "value" => [
              "fontFamily" => "valueicon",
              "iconName" => "123456",
            ],
          ],
        ],
      ],
      [
        'type' => 'json_string',
        'decoded' => [
          "contentIcon" => [
            "differentParentKey" => [
              "fontFamily" => "differenticon",
              "iconName" => "789012",
            ],
          ],
        ],
      ],
      [
        'type' => 'json_string',
        'decoded' => [
          "contentIcon" => [
            "value" => [
      // This won't be found because "iconName" sibling is not set.
              "fontFamily" => "valuenoiconname",
            ],
          ],
        ],
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new IconLibrary([], 'cohesion_icon_library'));

    // Check the results.
    $this->assertEquals(count($entities), 2);
    $this->assertEquals($entities[0]['uuid'], 'uuid=valueicon');
    $this->assertEquals($entities[1]['uuid'], 'uuid=differenticon');
  }

}
