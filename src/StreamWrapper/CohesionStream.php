<?php

namespace Drupal\cohesion\StreamWrapper;

use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\PublicStream;

/**
 * DO NOT USE
 * Only for backward compatibility.
 *
 * Class CohesionStream.
 */
class CohesionStream extends PublicStream {

  /**
   *
   */
  public static function basePath($site_path = NULL) {
    return Settings::get('file_public_path', parent::basePath($site_path) . '/cohesion');
  }

}
