<?php

namespace Drupal\cohesion_sync\EventSubscriber\Import;

use Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\file\FileInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Handles missing content dependencies such as files on config sync import.
 */
class SiteStudioSyncFilesSubscriber implements EventSubscriberInterface {

  const ERROR_MESSAGE = 'Unable to write "%s" file to "%s", please check your permissions.';

  /**
   * EntityTypeManager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
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
   * New files count.
   *
   * @var int
   */
  protected $newFiles = 0;

  /**
   * Updated files count.
   *
   * @var int
   */
  protected $updatedFiles = 0;

  /**
   * @var \Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent
   */
  protected $event;

  /**
   * SiteStudioConfigSyncMissingContent constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   EntityTypeManager service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   FileSystem service.
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FileSystemInterface $fileSystem,
    LoggerChannelFactoryInterface $loggerChannelFactory
  ) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
    $this->logger = $loggerChannelFactory->get('cohesion_sync');
  }

  /**
   * {@inheritDoc}
   */
  public static function getSubscribedEvents() {
    $events[SiteStudioSyncFilesEvent::IMPORT][] = ['handleFileImport', 50];
    $events[SiteStudioSyncFilesEvent::CHANGES][] = ['handleFileChanges', 50];
    return $events;
  }

  /**
   * Handles file import.
   *
   * @param \Drupal\cohesion_sync\Event\SiteStudioSyncFilesEvent $event
   *   Site Studio Sync Files Event.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function handleFileImport(SiteStudioSyncFilesEvent $event) {
    $this->event = $event;
    foreach ($event->getFiles() as $key => $file) {
      $destination = substr($file['uri'], 0, strlen($file['uri']) - strlen($file['filename']));
      if ($this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY) === FALSE) {
        $event->stopPropagation();
        $error_message = sprintf(self::ERROR_MESSAGE, $file['filename'], $destination, $key);
        $this->logger->alert($error_message);
        throw new \Exception($error_message);
      }

      $existing_file = $this->loadExistingFile($file['uuid']);

      if ($existing_file instanceof FileInterface) {
        $this->handleExistingFile($existing_file, $file);
      }
      else {
        $this->handleNewFile($file);
      }
    }

    $this->handleMessages();
  }

  public function handleFileChanges(SiteStudioSyncFilesEvent $event) {
    $this->event = $event;
    $this->updatedFiles = [];
    $this->newFiles = [];
    foreach ($event->getFiles() as $key => $file) {
      $existing_file = $this->loadExistingFile($file['uuid']);

      if ($existing_file instanceof FileInterface) {
        $incoming_file_path = $this->event->getPath() . '/' . $file['filename'];
        if ($this->fileHasChanges($existing_file, $incoming_file_path)) {
          $this->updatedFiles[] = $existing_file->label();
        }
      }
      else {
        $this->newFiles[] = $file['filename'];
      }
    }

    $this->handleMessages();
  }

  /**
   * Updates existing file if content hash is not identical.
   *
   * @param \Drupal\file\FileInterface $existing_file
   *   Existing file entity.
   * @param array $incoming_file
   *   Array with incoming file metadata from files index.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function handleExistingFile(FileInterface $existing_file, array $incoming_file) {
    $incoming_file_path = $this->event->getPath() . '/' . $incoming_file['filename'];
    if ($this->fileHasChanges($existing_file, $incoming_file_path)) {
      $this->fileSystem->copy($incoming_file_path, $incoming_file['uri'], FileSystemInterface::EXISTS_REPLACE);
      $existing_file->setChangedTime(time());
      $existing_file->setSize(filesize($incoming_file_path));
      $existing_file->save();
      $this->updatedFiles++;
    }
  }

  /**
   * Return a bool is the input file in different from the existing file
   *
   * @param $existing_file
   * @param $incoming_file_path
   *
   * @return bool
   */
  protected function fileHasChanges($existing_file, $incoming_file_path) {
    $existing_file_hash = md5(file_get_contents($existing_file->getFileUri()));
    $incoming_file_hash = md5(file_get_contents($incoming_file_path));
    return $existing_file_hash !== $incoming_file_hash;
  }

  /**
   * Copies new file to correct location and creates File entity in Drupal.
   *
   * @param array $incoming_file
   *   Array with incoming file metadata from files index.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function handleNewFile(array $incoming_file) {
    $this->fileSystem->copy(
      $this->event->getPath() . '/' . $incoming_file['filename'],
      $incoming_file['uri'],
      FileSystemInterface::EXISTS_REPLACE
    );

    unset($incoming_file['fid']);
    $entity = $this->entityTypeManager
      ->getStorage('file')
      ->create($incoming_file);
    $entity->save();
    $this->newFiles++;
  }

  /**
   * Sets Drupal static variable with counts of files imported/updated.
   */
  protected function handleMessages() {
    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');
    $cohesion_file_sync_messages['new_files'] = $this->newFiles;
    $cohesion_file_sync_messages['updated_files'] = $this->updatedFiles;
  }

  /**
   * Loads existing file entity by uuid.
   *
   * @param string $uuid
   *   Entity uuid.
   *
   * @return \Drupal\file\FileInterface|null
   *   File entity or null.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function loadExistingFile(string $uuid) {
    $results = $this->entityTypeManager->getStorage('file')
      ->loadByProperties(['uuid' => $uuid]);

    return reset($results);
  }

}
