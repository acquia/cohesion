<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;
use Drupal\cohesion\Plugin\Usage\FileUsage;
use Drupal\Core\Entity\EntityInterface;
use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;

/**
 * Mock for the core File entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MockFile extends CohesionConfigEntityBase implements CohesionSettingsInterface {
  public function getApiPluginInstance(){
  }
}

/**
 * @group Cohesion
 */
class FileUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Init the plugin.
    $this->unit = new FileUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\FileUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisType() {

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          "contentIcon" => [
            "value1" => [
              "subkey" => "public://some file with spaces.jpg",
            ],
            "value2" => [
              "subkey" => "START: public://someotherfile.jpg :END",
            ],
          ]
        ])
      ],
      [
        'type' => 'json_string',
        'value' => json_encode([
          "contentIcon" => [
            "value1" => [
              "subkey" => "[media-reference:file:somemediareferenceuuid]",
            ],
            "value2" => [
              "subkey" => "START: [media-reference:file:someothermediareferenceuuid] :END",
            ],
          ]
        ])
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MockFile([], 'file'));

    // Check the results.
    $this->assertEquals(count($entities), 4);
    $this->assertEquals($entities[0]['uuid'], 'uuid=uri-public://some file with spaces.jpg');
    $this->assertEquals($entities[1]['uuid'], 'uuid=uri-public://someotherfile.jpg');
    $this->assertEquals($entities[2]['uuid'], 'uuid=uuid-somemediareferenceuuid');
    $this->assertEquals($entities[3]['uuid'], 'uuid=uuid-someothermediareferenceuuid');
  }

}
