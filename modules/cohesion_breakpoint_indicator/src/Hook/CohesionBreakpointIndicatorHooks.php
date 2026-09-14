<?php

namespace Drupal\cohesion_breakpoint_indicator\Hook;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Hook implementations for Cohesion breakpoint indicator.
 */
class CohesionBreakpointIndicatorHooks {

  public function __construct(
    protected AccountInterface $current_user,
    protected ConfigFactoryInterface $config_factory,
    protected ThemeManagerInterface $theme_manager,
  ) {}

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function page_attachments(array &$attachments) {
    // Load library if user has permission.
    $permission = $this->current_user->hasPermission('access cohesion breakpoint indicator');
    $current_theme = $this->theme_manager->getActiveTheme();
    $is_admin = $this->config_factory->get('system.theme')->get('admin') == $current_theme->getName();

    // Check if the user has permission & not on an admin url.
    if ($permission && !$is_admin) {
      $attachments['#attached']['library'][] = 'cohesion_breakpoint_indicator/cohesion-breakpoint-indicator';
    }
  }

}
