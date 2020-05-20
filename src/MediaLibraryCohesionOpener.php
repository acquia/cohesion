<?php

namespace Drupal\cohesion;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;
use Drupal\media_library\MediaLibraryEditorOpener;
use Drupal\media_library\MediaLibraryState;

/**
 * Class MediaLibraryCohesionOpener
 *
 * @package Drupal\media_library
 */
class MediaLibraryCohesionOpener extends MediaLibraryEditorOpener {

  public function checkAccess(MediaLibraryState $state, AccountInterface $account) {
    return AccessResult::allowed();
  }

}
