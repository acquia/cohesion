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

  const FILENAMES_MAP = [
    'f912fea9-fd41-4f12-bee2-90f6a27219bf' => 'image_1.png',
    'f912fea9-fd41-4f12-bee2-90f6a27219bg' => 'image_2.png',
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
      drupal_get_path('module', 'cohesion_sync')
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
  protected function setUp() {
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
        if (isset(self::FILENAMES_MAP[$argument['uuid']])) {
          $fileMock = $this->getMockBuilder(FileInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
          $fileMock->expects($this->any())
            ->method('getFileUri')
            ->will($this->returnValue($this->siteDirectory . '/files/' . self::FILENAMES_MAP[$argument['uuid']]));
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
    file_put_contents($this->siteDirectory . '/files/image_1.png', $modified_file);

    $file = file_get_contents($this->getPackageFixturePath() . '/image_2.png');
    file_put_contents($this->siteDirectory . '/files/image_2.png', $file);
  }

  /**
   * @covers Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleExistingFile
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleExistingFile() {
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1_original.png', $this->siteDirectory . '/files/image_1.png');
    $event_subscriber = $this->createEventSubscriber();
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1_original.png', $this->siteDirectory . '/files/image_1.png');

    $incoming_file = [
      "fid" => "261",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bf",
      "langcode" => "en",
      "filename" => "image_1.png",
      "uri" => $this->siteDirectory . '/files/image_1.png',
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
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1.png', $this->siteDirectory . '/files/image_1.png');
  }

  /**
   * @covers Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleNewFile
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleNewFile() {
    $this->assertFileNotExists($this->siteDirectory . '/files/image_4.png');
    $event_subscriber = $this->createEventSubscriber();
    $this->assertFileNotExists($this->siteDirectory . '/files/image_4.png');

    $incoming_file = [
      "fid" => "261",
      "uuid" => "f912fea9-fd41-4f12-bee2-90f6a27219bg",
      "langcode" => "en",
      "filename" => "image_4.png",
      "uri" => $this->siteDirectory . '/files/image_4.png',
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
    $this->assertFileExists($this->siteDirectory . '/files/image_2.png');
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_2.png', $this->siteDirectory . '/files/image_2.png');
  }

  /**
   * @covers Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleFileImport
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function testHandleFileImport() {

    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1_original.png', $this->siteDirectory . '/files/image_1.png');
    $this->createEventSubscriber(FALSE);
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1.png', $this->siteDirectory . '/files/image_1.png');

    $this->assertFileEquals($this->getPackageFixturePath() . '/image_1.png', $this->siteDirectory . '/files/image_1.png');

    $this->assertFileExists($this->siteDirectory . '/files/image_1.png');
    $this->assertFileExists($this->siteDirectory . '/files/image_2.png');
    $this->assertFileExists($this->siteDirectory . '/files/image_3.png');
    $this->assertFileNotExists($this->siteDirectory . '/files/image_4.png');

    $this->assertFileEquals($this->getPackageFixturePath() . '/image_2.png', $this->siteDirectory . '/files/image_2.png');
    $this->assertFileEquals($this->getPackageFixturePath() . '/image_3.png', $this->siteDirectory . '/files/image_3.png');

    $cohesion_file_sync_messages['new_files'] = $this->newFiles;
    $cohesion_file_sync_messages['updated_files'] = $this->updatedFiles;
  }

  /**
   * @covers Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::loadExistingFile
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
   * @covers Drupal\cohesion_sync\EventSubscriber\Import\SiteStudioSyncFilesSubscriber::handleMessages
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
    $this->assertEqual($cohesion_file_sync_messages['new_files'], 0);
    $this->assertEqual($cohesion_file_sync_messages['updated_files'], 0);

    $this->createEventSubscriber(FALSE);
    $this->assertArrayHasKey('new_files', $cohesion_file_sync_messages);
    $this->assertArrayHasKey('updated_files', $cohesion_file_sync_messages);
    $this->assertEqual($cohesion_file_sync_messages['new_files'], 1);
    $this->assertEqual($cohesion_file_sync_messages['updated_files'], 1);
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
