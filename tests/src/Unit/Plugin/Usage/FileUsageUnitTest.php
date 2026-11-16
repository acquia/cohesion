<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\FileUsage;
use Drupal\file\FileInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTest;
use Prophecy\Argument;

/**
 * Mock for the core File entity.
 *
 * @package Drupal\Tests\cohesion\Unit\Plugin\Usage
 */
class MockFile extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  public function getApiPluginInstance() {
  }

}

/**
 * @group Cohesion
 */
class FileUsageUnitTest extends UsagePluginBaseUnitTest {

  /**
   * @var \Drupal\file\FileRepositoryInterface
   */
  private $file_repository;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $fileMock = $this->getMockBuilder(FileInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $fileMock->expects($this->any())
      ->method('uuid')
      ->willReturn(NULL);
    $fileMock->expects($this->any())
      ->method('setPermanent')
      ->willReturn(NULL);
    $fileMock->expects($this->any())
      ->method('save')
      ->willReturn(1);

    $prophecy = $this->prophesize(FileRepositoryInterface::class);
    $prophecy->writeData(
      Argument::type('string'),
      Argument::type('string'),
      Argument::type('integer')
    )->willReturn($fileMock);

    $this->file_repository = $prophecy->reveal();

    // Init the plugin.
    $this->unit = new FileUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock,
      $this->file_repository
    );
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
          ],
        ]),
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
          ],
        ]),
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
