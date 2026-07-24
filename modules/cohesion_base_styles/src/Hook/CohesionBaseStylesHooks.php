<?php

namespace Drupal\cohesion_base_styles\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for Cohesion Base styles.
 */
class CohesionBaseStylesHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name) {
    switch ($route_name) {
      // Main module help for the cohesion_base_styles module.
      case 'help.page.cohesion_base_styles':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('This module defines the base style config entities for creating HTML element base styles.') . '</p>';
        $output .= '<p><ul>';
        $output .= '  <li>Base style configuration entity and supporting forms.</li>';
        $output .= '  <li>Site Studio API integration.</li>';
        $output .= '</ul></p>';
        $output .= '<p><a href="https://docs.acquia.com/" target="_blank">https://docs.acquia.com/</a></p>';
        return $output;

      default:
    }
  }

}
