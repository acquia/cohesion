<?php

namespace Drupal\Tests\cohesion_templates\Unit\Plugin\Usage;

use Drupal\cohesion_templates\Entity\ViewTemplates;
use Drupal\cohesion_templates\Plugin\Usage\ViewTemplatesUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class ViewTemplatesUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ViewTemplatesUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_templates\Plugin\Usage\ViewTemplatesUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    // Note that the only place a view template is used/set is within a view.
    $fixture = [
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_view_template',
        'id' => 'view-template-id',
      ],
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_view_template',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new ViewTemplates([], 'cohesion_view_templates'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'uuid=view-template-id');
  }

}
