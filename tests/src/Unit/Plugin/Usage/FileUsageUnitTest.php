<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\Usage;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Plugin\Usage\FileUsage;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\file\FileInterface;
use Drupal\file\FileRepositoryInterface;
use Drupal\Tests\cohesion\Unit\UsagePluginBaseUnitTestCase;
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
 * Stream wrapper stub under which no file ever exists.
 */
class MissingFileStreamWrapper {

  /**
   * Stream context, set by PHP.
   *
   * @var resource|null
   */
  public $context;

  /**
   * Reports every path as missing.
   */
  public function url_stat($path, $flags) {
    return FALSE;
  }

}

/**
 * @group Cohesion
 */
class FileUsageUnitTest extends UsagePluginBaseUnitTestCase {

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
  public function testScanForInstancesOfThisType(): void {

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

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\FileUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisTypeDecodesUrlEncodedUri(): void {
    // CKEditor 5's upload response URL-encodes the file path (e.g. a space
    // becomes %20), and that encoded string is what gets saved into the
    // component's stored content. The real file.uri in file_managed is
    // never encoded, so the lookup must decode the matched URI first.
    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          "contentIcon" => [
            "value1" => [
              "subkey" => "public://some%20file%20with%20spaces.jpg",
            ],
          ],
        ]),
      ],
    ];

    $entities = $this->unit->scanForInstancesOfThisType($fixture, new MockFile([], 'file'));

    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'uuid=uri-public://some file with spaces.jpg');
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\FileUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisTypeIgnoresEncodedUriWithoutRealFile(): void {
    // An encoded URI is decoded before the lookup. When the decoded URI
    // matches neither a managed file nor a file on disk, no file must be
    // created and no usage registered.
    $storage = $this->prophesize(EntityStorageInterface::class);
    $storage->loadByProperties(['uri' => 'public://does not exist.jpg'])
      ->shouldBeCalledOnce()
      ->willReturn([]);
    $storage->loadByProperties(['uri' => 'public://does%20not%20exist.jpg'])
      ->shouldNotBeCalled();

    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage($this->plugin_definition['entity_type'])
      ->willReturn($storage->reveal());

    $file_repository = $this->prophesize(FileRepositoryInterface::class);
    $file_repository->writeData(Argument::any(), Argument::any(), Argument::any())->shouldNotBeCalled();

    $unit = new FileUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $entity_type_manager->reveal(),
      $this->stream_wrapper_manager_mock,
      $this->database_connection_mock,
      $file_repository->reveal()
    );

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          "contentIcon" => [
            "value1" => [
              "subkey" => "public://does%20not%20exist.jpg",
            ],
          ],
        ]),
      ],
    ];

    // The decoded URI must not exist on disk, so register a wrapper for the
    // scheme under which nothing exists.
    $this->assertTrue(stream_wrapper_register('public', MissingFileStreamWrapper::class));
    try {
      $entities = $unit->scanForInstancesOfThisType($fixture, new MockFile([], 'file'));
    }
    finally {
      stream_wrapper_unregister('public');
    }

    $this->assertSame([], $entities);
  }

  /**
   * @covers \Drupal\cohesion\Plugin\Usage\FileUsage::scanForInstancesOfThisType
   */
  public function testScanForInstancesOfThisTypeResolvesSharedBasepathToPublic(): void {
    // Simulate Drupal core registering 'assets' ahead of 'public' (as it
    // does from Drupal 10.1), with both wrappers resolving to the same
    // physical directory.
    $prophecy = $this->prophesize(StreamWrapperManagerInterface::class);
    $prophecy->getWrappers(StreamWrapperInterface::LOCAL)->willReturn([
      'assets' => [],
      'public' => [],
    ]);
    $prophecy->getViaUri(Argument::type('string'))->will(function ($args) {
      return new class {

        public function getDirectoryPath() {
          return 'sites/default/files';
        }

      };
    });

    $unit = new FileUsage(
      $this->configuration,
      $this->plugin_id,
      $this->plugin_definition,
      $this->entity_type_manager_mock,
      $prophecy->reveal(),
      $this->database_connection_mock,
      $this->file_repository
    );

    $fixture = [
      [
        'type' => 'json_string',
        'value' => json_encode([
          "contentIcon" => [
            "value1" => [
              "subkey" => "/sites/default/files/some-file.jpg",
            ],
          ],
        ]),
      ],
    ];

    $entities = $unit->scanForInstancesOfThisType($fixture, new MockFile([], 'file'));

    $this->assertEquals(count($entities), 1);
    $this->assertEquals($entities[0]['uuid'], 'uuid=uri-public://some-file.jpg');
  }

}
