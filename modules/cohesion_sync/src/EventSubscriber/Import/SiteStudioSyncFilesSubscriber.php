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

  const URI_ERROR_MESSAGE = 'Unable to parse uri "%s" for file "%s".';
  const WRITE_ERROR_MESSAGE = 'Unable to write "%s" file to "%s", please check your permissions.';
  const FILENAME_PATTERN = '%[a-zA-Z0-9\.\-\+\_ \(\)\[\]]+$%';

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
    LoggerChannelFactoryInterface $loggerChannelFactory,
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
    foreach ($event->getFiles() as $file) {
      $uri_filename = $this->getUriFilename($file['uri']);
      if (empty($uri_filename)) {
        $event->stopPropagation();
        $error_message = sprintf(self::URI_ERROR_MESSAGE, $file['filename'], $file['uri']);
        $this->logger->alert($error_message);
        throw new \Exception($error_message);
      }

      $destination = substr($file['uri'], 0, strlen($file['uri']) - strlen($uri_filename));
      if ($this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY) === FALSE) {
        $event->stopPropagation();
        $error_message = sprintf(self::WRITE_ERROR_MESSAGE, $file['filename'], $destination);
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
    foreach ($event->getFiles() as $file) {
      $existing_file = $this->loadExistingFile($file['uuid']);

      if ($existing_file instanceof FileInterface) {
        if ($this->fileHasChanges($existing_file, $file)) {
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
    $incoming_file_path = $this->getIncomingFilePath($incoming_file);
    if ($this->fileHasChanges($existing_file, $incoming_file)) {
      $this->fileSystem->copy($incoming_file_path, $incoming_file['uri'], FileSystemInterface::EXISTS_REPLACE);
      $existing_file->setSize(filesize($incoming_file_path));
      if ($existing_file->getFileUri() != $incoming_file['uri']) {
        $this->fileSystem->delete($existing_file->getFileUri());
        $existing_file->setFileUri($incoming_file['uri']);
      }
      $existing_file->setFilename($incoming_file['filename']);
      $existing_file->setMimeType($incoming_file['filemime']);
      $existing_file->setChangedTime(time());
      $existing_file->save();
      $this->updatedFiles++;
    }
  }

  /**
   * Return a bool is the input file in different from the existing file.
   *
   * @param \Drupal\file\FileInterface $existing_file
   *   Existing File entity.
   * @param array $incoming_file
   *   Incoming file metadata from package.
   *
   * @return bool
   *   TRUE if changes in actual file or some of the metadata is detected.
   */
  protected function fileHasChanges(FileInterface $existing_file, array $incoming_file): bool {
    $incoming_file_path = $this->getIncomingFilePath($incoming_file);

    $existing_file_hash = md5(file_get_contents($existing_file->getFileUri()));
    $incoming_file_hash = md5(file_get_contents($incoming_file_path));

    $changed = $existing_file_hash !== $incoming_file_hash;

    if ($changed === FALSE) {
      $changed = $existing_file->getFileUri() !== $incoming_file['uri'];
      $changed = $changed ?: $existing_file->getFilename() !== $incoming_file['filename'];
      $changed = $changed ?: $existing_file->getMimeType() !== $incoming_file['filemime'];
      $changed = $changed ?: $existing_file->getSize() !== (int) $incoming_file['filesize'];
    }

    return $changed;
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
      $this->getIncomingFilePath($incoming_file),
      $incoming_file['uri'],
      FileSystemInterface::EXISTS_REPLACE
    );

    if (isset($incoming_file['fid'])) {
      unset($incoming_file['fid']);
    }
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

  /**
   * Returns path to the incoming file.
   *
   * @param array $incoming_file
   *   Array of incoming file metadata.
   *
   * @return string
   *   Path to the file in package location.
   */
  protected function getIncomingFilePath(array $incoming_file) {
    return $this->event->getPath() . '/' . $incoming_file['filename'];
  }

  /**
   * Finds and returns the filename from URI string.
   *
   * @param string $uri
   *   Uri string.
   *
   * @return string
   *   Filename part of the URI string.
   */
  protected function getUriFilename(string $uri): string {
    $result = preg_match(self::FILENAME_PATTERN, $uri, $outcome);
    if ($result === 1) {
      return $outcome[0];
    }

    return '';
  }

}
