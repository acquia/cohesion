<?php

namespace Drupal\Tests\cohesion_sync\Kernel\EventSubscriber\Import;

use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
use Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\Entity\File;
use Drupal\file\FileInterface;
use Drupal\Tests\cohesion\Unit\SiteStudioTestProtectedPrivateMethodsTrait;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use org\bovigo\vfs\vfsStreamWrapper;

/**
 * Tests for handling files during Package import.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion_sync\EventSubscriber\Import
 *
 * @covers \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber
 */
class SiteStudioSyncFilesSubscriberTest extends EntityKernelTestBase {

  use SiteStudioTestProtectedPrivateMethodsTrait;

  const FILES = [
    // This entry has the file different to the one in the package and will be updated on import.
    "f912fea9-fd41-4f12-bee2-90f6a27219bf" => [
      "fid" => "4",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bf",
      "langcode" => "en",
      "filename" => "image_1.png",
      "uri" => "public://image_1.png",
      "filemime" => "image/png",
      "filesize" => "4594",
      "status" => "1",
      "created" => "1634141175",
      "changed" => "1634141175",
    ],
    // This entry has the filesize different to the one in the package and will be updated on import.
    "f912fea9-fd41-4f12-bee2-90f6a27219bg" => [
      "fid" => "5",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bg",
      "langcode" => "en",
      "filename" => "image_2.png",
      "uri" => "public://image_2.png",
      "filemime" => "image/png",
      "filesize" => "0",
      "status" => "1",
      "created" => "1634141177",
      "changed" => "1634141177",
    ],
    // This entry is identical to the one in the package and will not be updated.
    "f912fea9-fd41-4f12-bee2-90f6a27219bj" => [
      "fid" => "7",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bj",
      "langcode" => "en",
      "filename" => "image_5.png",
      "uri" => "public://image_5.png",
      "filemime" => "image/png",
      "filesize" => "4476",
      "status" => "1",
      "created" => "1634141178",
      "changed" => "1634141178",
    ],
  ];

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'cohesion',
    'cohesion_elements',
    'cohesion_templates',
    'cohesion_sync',
    'file',
    'context',
    'image',
    'user',
    'entity_reference_revisions',
  ];

  /**
   * Returns fixture content.
   *
   * @inheritDoc
   */
  protected function getPackageFixturePath() {
    return sprintf("%s/tests/fixtures/test_package",
      $this->container->get('extension.path.resolver')->getPath('module', 'cohesion_sync')
    );
  }

  /**
   * EntityTypeManager Mock.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * FileSystem service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Sets up modules, schema and fake pre-existing image files.
   *
   * @throws \org\bovigo\vfs\vfsStreamException
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);

    $this->installEntitySchema('component_content');
    $this->installSchema('cohesion', ['coh_usage']);

    vfsStreamWrapper::register();

    $entityStorage = $this->getMockBuilder(EntityStorageInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityStorage->expects($this->any())
      ->method('loadByProperties')
      ->willReturnCallback(function ($argument) {
        $fileMock = NULL;
        if (isset(self::FILES[$argument['uuid']])) {
          $fileMock = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
          $fileMock->expects($this->any())
            ->method('getFileUri')
            ->will($this->returnValue(self::FILES[$argument['uuid']]['uri']));
          $fileMock->expects($this->any())
            ->method('getFilename')
            ->will($this->returnValue(self::FILES[$argument['uuid']]['filename']));
          $fileMock->expects($this->any())
            ->method('getMimeType')
            ->will($this->returnValue(self::FILES[$argument['uuid']]['filemime']));
          $fileMock->expects($this->any())
            ->method('getSize')
            ->will($this->returnValue((int) self::FILES[$argument['uuid']]['filesize']));
        }

        return [$fileMock];
      });
    $entityStorage->expects($this->any())
      ->method('create')
      ->willReturnCallback(function ($argument) {
        return File::create($argument);
      });

    $entityTypeManager = $this->getMockBuilder(EntityTypeManagerInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $entityTypeManager->expects($this->any())
      ->method('getStorage')
      ->willReturn($entityStorage);
    $this->entityTypeManager = $entityTypeManager;

    $this->logger = $this->getMockBuilder(LoggerChannelFactoryInterface::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->setUpFilesystem();
    $this->fileSystem = $this->container->get('file_system');

    // Ensure pre-exiting image_1 is different to the one in package directory.
    $modified_file = file_get_contents($this->getPackageFixturePath() . '/image_1_original.png');
    file_put_contents('public://image_1.png', $modified_file);

    $file = file_get_contents($this->getPackageFixturePath() . '/image_2.png');
    file_put_contents('public://image_2.png', $file);

    $extra_file = file_get_contents($this->getPackageFixturePath() . '/image_5.png');
    file_put_contents('public://image_5.png', $extra_file);
  }

  /**
   * @covers \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleExistingFile
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleExistingFile() {
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1_original.png', 'public://image_1.png');
    $event_subscriber = $this->createEventSubscriber();
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1_original.png', 'public://image_1.png');

    $incoming_file = [
      "fid" => "261",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bf",
      "langcode" => "en",
      "filename" => "image_1.png",
      "uri" => 'public://image_1.png',
      "filemime" => "image\/png",
      "filesize" => "7834",
      "status" => "1",
      "created" => "1562079215",
      "changed" => "1562079218",
    ];

    $existing_file = File::create($incoming_file);
    $existing_file->setFileUri($incoming_file['uri']);
    $this->callMethod($event_subscriber, 'handleExistingFile', [
      $existing_file,
      $incoming_file,
    ]);
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1.png', 'public://image_1.png');
  }

  /**
   * @covers \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleNewFile
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleNewFile() {
    $this->assertFileDoesNotExist('public://image_4.png');
    $event_subscriber = $this->createEventSubscriber();
    $this->assertFileDoesNotExist('public://image_4.png');

    $incoming_file = [
      "fid" => "261",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bg",
      "langcode" => "en",
      "filename" => "image_4.png",
      "uri" => 'public://image_4.png',
      "filemime" => "image\/png",
      "filesize" => "4476",
      "status" => "1",
      "created" => "1562079215",
      "changed" => "1562079218",
    ];

    $existing_file = File::create($incoming_file);
    $existing_file->setFileUri($incoming_file['uri']);
    $this->callMethod($event_subscriber, 'handleNewFile', [
      $incoming_file,
    ]);
    $this->assertFileExists('public://image_2.png');
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_2.png', 'public://image_2.png');
  }

  /**
   * @covers \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleFileImport
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleFileImport() {

    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1_original.png', 'public://image_1.png');
    $this->createEventSubscriber(FALSE);
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1.png', 'public://image_1.png');

    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1.png', 'public://image_1.png');

    $this->assertFileExists('public://image_1.png');
    $this->assertFileExists('public://image_2.png');
    $this->assertFileExists('public://image_3.png');
    $this->assertFileDoesNotExist('public://image_4.png');
    $this->assertFileExists('public://image_5.png');

    $this->assertFileEquals($this->getPackageFixturePath() . '/image_2.png', 'public://image_2.png');
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_3.png', 'public://image_3.png');
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_5.png', 'public://image_5.png');
  }

  /**
   * @covers \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::loadExistingFile
   */
  public function testLoadExistingFile() {
    $event_subscriber = new SiteStudioSyncFilesSubscriber(
      $this->entityTypeManager,
      $this->fileSystem,
      $this->logger,
    );

    $this->assertInstanceOf(FileInterface::class, $this->callMethod($event_subscriber, 'loadExistingFile', ['f912fea9-fd41-4f12-bee2-90f6a27219bf']));
  }

  /**
   * @covers \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleMessages
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleMessages() {
    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');
    $this->createEventSubscriber();
    $this->assertArrayHasKey('new_files', $cohesion_file_sync_messages);
    $this->assertArrayHasKey('updated_files', $cohesion_file_sync_messages);
    $this->assertEquals(0, $cohesion_file_sync_messages['new_files']);
    $this->assertEquals(0, $cohesion_file_sync_messages['updated_files']);

    $this->createEventSubscriber(FALSE);
    $this->assertArrayHasKey('new_files', $cohesion_file_sync_messages);
    $this->assertArrayHasKey('updated_files', $cohesion_file_sync_messages);
    $this->assertEquals(1, $cohesion_file_sync_messages['new_files']);
    $this->assertEquals(2, $cohesion_file_sync_messages['updated_files']);
  }

  /**
   * Creates Sync Files Event Subscriber.
   *
   * Uses ::handleFileImport() to populate protected $path property.
   *
   * @param bool $empty
   *   TRUE if files array should be empty.
   *
   * @return \Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createEventSubscriber(bool $empty = TRUE) {
    if ($empty) {
      $files = [];
    }
    else {
      $files = file_get_contents($this->getPackageFixturePath() . '/sitestudio_package_files.json');
      $files = json_decode($files, TRUE);
    }
    $event = new SiteStudioSyncFilesEvent($files, $this->getPackageFixturePath());
    $event_subscriber = new SiteStudioSyncFilesSubscriber(
      $this->entityTypeManager,
      $this->fileSystem,
      $this->logger,
    );
    $event_subscriber->handleFileImport($event);

    return $event_subscriber;
  }

}
