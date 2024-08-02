<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\Core\Site\Settings;

/**
 * Provides a factory for creating config file storage objects.
 */
class CohesionFileStorageFactory {

  /**
   * Returns a CohesionFileStorage object with export directory.
   *
   * @return \Drupal\cohesion_sync\Config\CohesionFileStorage FileStorage
   *
   * @throws \Exception
   *   In case the site studio sync directory does not exist or is not
   *   defined in $settings['site_studio_sync'].
   */
  public static function buildExportFileStorage() {
    $directory = Settings::get('site_studio_sync', COHESION_SYNC_DEFAULT_DIR);

    return new CohesionFileStorage($directory);
  }

  /**
   * Returns a CohesionFileStorage object with import directory.
   *
   * @return \Drupal\cohesion_sync\Config\CohesionFileStorage FileStorage
   */
  public static function buildImportFileStorage() {
    $directory = _cohesion_sync_temp_directory();

    return new CohesionFileStorage($directory);
  }

}
