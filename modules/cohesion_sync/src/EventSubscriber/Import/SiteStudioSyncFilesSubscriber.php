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

  const ERROR_MESSAGE = 'Unable to write "%s" file to "%s", resulting in config entity "%s" imported with missing file dependency.';

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
    $events[SiteStudioSyncFilesEvent::NAME][] = ['handleFileImport', 50];
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
    $path = $event->getPath();
    $new = 0;
    $updated = 0;
    foreach ($event->getFiles() as $key => $file) {
      $source = $path . '/' . $file['filename'];
      $destination = substr($file['uri'], 0, strlen($file['uri']) - strlen($file['filename']));
      if ($this->fileSystem->prepareDirectory($destination, FileSystemInterface::CREATE_DIRECTORY) === FALSE) {
        $this->logger->alert(sprintf(self::ERROR_MESSAGE, $file['filename'], $destination, $key));
        continue;
      }

      $existing_file = $this->loadExistingFile($file['uuid']);

      if ($existing_file instanceof FileInterface) {
        $existing_file_hash = md5(file_get_contents($existing_file->getFileUri()));
        $new_file_hash = md5(file_get_contents($source));
        if ($existing_file_hash !== $new_file_hash) {
          $this->fileSystem->copy($source, $file['uri'], FileSystemInterface::EXISTS_REPLACE);
          $existing_file->setChangedTime(time())
            ->setSize(filesize($source));
          $existing_file->save();
          $updated++;
        }
      }
      else {
        $this->fileSystem->copy($source, $file['uri'], FileSystemInterface::EXISTS_REPLACE);
        unset($file['fid']);
        $entity = $this->entityTypeManager
          ->getStorage('file')
          ->create($file);
        $entity->save();
        $new++;
      }
    }
    $cohesion_file_sync_messages = &drupal_static('cohesion_file_sync_messages');
    $cohesion_file_sync_messages['new_files'] = $new;
    $cohesion_file_sync_messages['updated_files'] = $updated;
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
