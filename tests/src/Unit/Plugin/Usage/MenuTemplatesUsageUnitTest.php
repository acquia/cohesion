<?php

namespace Drupal\Tests\cohesion_templates\Unit\Plugin\Usage;

use Drupal\cohesion_templates\Entity\MenuTemplates;
use Drupal\cohesion_templates\Plugin\Usage\MenuTemplatesUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class MenuTemplatesUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new MenuTemplatesUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_templates\Plugin\Usage\MenuTemplatesUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'decoded' => [
          "model" => [
            "menu" => [
              "id" => "someid",
              "template" => "menu-template-id",
            ],
          ],
        ],
      ],
      [
        'type' => 'json_string',
        'decoded' => [
          "model" => [
            "menu" => [
              "id" => "will not find this",
            ],
          ],
        ],
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MenuTemplates([], 'cohesion_menu_templates'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'uuid=menu-template-id');
  }

}
