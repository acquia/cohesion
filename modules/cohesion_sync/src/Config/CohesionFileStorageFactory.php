<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\Core\Site\Settings;

/**
 * Provides a factory for creating config file storage objects.
 */
class CohesionFileStorageFactory {

  /**
   * Returns a CohesionFileStorage object working with the sync config
   * directory.
   *
   * @return \Drupal\cohesion_sync\Config\CohesionFileStorage FileStorage
   *
   * @throws \Exception
   *   In case the site studio sync directory does not exist or is not
   *   defined in $settings['site_studio_sync'].
   */
  public static function getSync() {
    $directory = Settings::get('site_studio_sync', COHESION_SYNC_DEFAULT_DIR);

    return new CohesionFileStorage($directory);
  }

}
