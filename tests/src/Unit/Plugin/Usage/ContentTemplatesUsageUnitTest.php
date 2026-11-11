<?php

namespace Drupal\Tests\cohesion_templates\Unit\Plugin\Usage;

use Drupal\cohesion_templates\Entity\ContentTemplates;
use Drupal\cohesion_templates\Plugin\Usage\ContentTemplatesUsage;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;

/**
 * @group Cohesion
 */
class ContentTemplatesUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    // Init the plugin.
    $this->unit = new ContentTemplatesUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion_templates\Plugin\Usage\ContentTemplatesUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_content_templates',
        'id' => 'id-green',
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new ContentTemplates([], 'cohesion_content_templates'));

    // Check the results.
    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'uuid=id-green');
  }

}
