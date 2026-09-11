<?php

namespace Drupal\cohesion_sync\Event;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Site Studio Sync Files event, used to handle files during package import.
 */
class SiteStudioSyncFilesEvent extends Event {

  /**
   * Name of the Event.
   */
  const IMPORT = 'cohesion_sync.import.files';

  const CHANGES = 'cohesion_sync.changes.files';

  /**
   * Files metadata.
   *
   * @var array
   */
  protected $files = [];

  /**
   * Patch where package files are located.
   *
   * @var string
   */
  protected $path = '';

  /**
   * SiteStudioSyncFilesEvent constructor.
   *
   * @param array $files
   *   Files metadata, keyed by dependency name.
   * @param string $path
   *   Path pointing to where package and files are located.
   */
  public function __construct(
    array $files,
    string $path,
  ) {
    $this->files = $files;
    $this->path = $path;
  }

  /**
   * Fetches files metadata.
   *
   * @return array
   *   Files metadata.
   */
  public function getFiles(): array {
    return $this->files;
  }

  /**
   * Fetches path.
   *
   * @return string
   *   Path to the package and files location.
   */
  public function getPath(): string {
    return $this->path;
  }

}
