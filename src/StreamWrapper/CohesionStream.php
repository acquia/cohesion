<?php

namespace Drupal\cohesion\StreamWrapper;

use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;

/**
 * DO NOT USE
 * Only for backward compatibility.
 *
 * Class CohesionStream.
 */
class CohesionStream extends PublicStream {

  /**
   * {@inheritdoc}
   */
  public static function getType() {
    return StreamWrapperInterface::LOCAL_HIDDEN;
  }

  /**
   * {@inheritdoc}
   */
  public static function basePath($site_path = NULL) {
    return Settings::get('file_public_path', parent::basePath($site_path) . '/cohesion');
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return t('Site Studio files');
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    return t('Site Studio local files served by the webserver.');
  }

}
