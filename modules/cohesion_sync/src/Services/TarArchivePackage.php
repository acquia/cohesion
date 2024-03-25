<?php

namespace Drupal\cohesion_sync\Services;

use Drupal\Core\Archiver\ArchiveTar;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Site\Settings;

/**
 * Handles packages archived in .tar format.
 */
class TarArchivePackage implements PackageSourceServiceInterface {

  const SUPPORTED_TYPE = 'tar_archive_package';
  const PATH_MISSING = 'Attempting handle package archived with .tar but "file_location" value is missing in metadata.';

  /**
   * Default Site Studio sync directory.
   *
   * @var string
   */
  protected $defaultSyncDir;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * @param \Drupal\Core\Site\Settings $settings
   *   The settings object.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(Settings $settings, FileSystemInterface $file_system) {
    $this->defaultSyncDir = _cohesion_sync_temp_directory();
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritDoc}
   */
  public function supportedType(string $type): bool {
    return $type === self::SUPPORTED_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedType(): string {
    return self::SUPPORTED_TYPE;
  }

  /**
   * Prepares package archived in .tar.
   *
   * This service extracts from .tar and moves them into the
   * default Site Studio sync directory. Once done, returns path
   * used as destination for files. Removes .tar file from filesystem.
   *
   * @param array $sourceMetadata
   *   Source metadata, contains path to the .tar file.
   *
   * @return string
   *   Path to the extracted package files.
   *
   * @throws \Exception
   *   If path value is missing in metadata or by Archiver if extraction fails.
   */
  public function preparePackage(array $sourceMetadata): string {
    if (isset($sourceMetadata['file_location'])) {
      $path = $sourceMetadata['file_location'];
    }
    else {
      throw new \Exception(self::PATH_MISSING);
    }

    $archiver = new ArchiveTar($path, 'gz');
    $files = [];
    foreach ($archiver->listContent() as $file) {
      $files[] = $file['filename'];
    }

    $archiver->extractList($files, $this->defaultSyncDir, '', FALSE, FALSE);
    $this->fileSystem->unlink($path);

    return $this->defaultSyncDir;
  }

}
