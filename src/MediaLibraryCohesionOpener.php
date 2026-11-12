<?php

namespace Drupal\cohesion;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\media_library\MediaLibraryEditorOpener;
use Drupal\media_library\MediaLibraryState;

/**
 * Site studio media library opener for image uploader/entity browsers.
 *
 * @package Drupal\media_library
 */
class MediaLibraryCohesionOpener extends MediaLibraryEditorOpener {

  /**
   * {@inheritdoc}
   */
  public function checkAccess(MediaLibraryState $state, AccountInterface $account) {
    return AccessResult::allowed();
  }

}
