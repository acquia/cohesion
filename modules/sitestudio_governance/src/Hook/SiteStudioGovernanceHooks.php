<?php

namespace Drupal\sitestudio_governance\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for Site Studio Governance.
 */
class SiteStudioGovernanceHooks {

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function page_attachments(array &$attachments) {
    $attachments['#attached']['drupalSettings']['cohesion']['extra_governance'] = TRUE;
  }

}
