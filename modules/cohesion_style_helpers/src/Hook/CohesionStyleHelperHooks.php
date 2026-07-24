<?php

namespace Drupal\cohesion_style_helpers\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for Cohesion style helpers.
 */
class CohesionStyleHelperHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name) {
    switch ($route_name) {
      // Main module help for the cohesion_style_helpers module.
      case 'help.page.cohesion_style_helpers':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('This module defines the style helper entity for creating reusable styles in Site Studio.') . '</p>';
        $output .= '<p><ul>';
        $output .= '  <li>Style helper configuration entity and supporting forms.</li>';
        $output .= '</ul></p>';
        $output .= '<p><a href="https://docs.acquia.com/" target="_blank">https://docs.acquia.com/</a></p>';
        return $output;

      default:
    }
  }

}
