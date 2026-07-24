<?php

namespace Drupal\cohesion_website_settings\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for Cohesion website settings.
 */
class CohesionWebsiteSettingsHooks {

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name) {
    switch ($route_name) {
      // Main module help for the cohesion_website_settings module.
      case 'help.page.cohesion_website_settings':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('This module defines the website settings config entities.') . '</p>';
        $output .= '<p><ul>';
        $output .= '  <li>Website settings configuration entity and supporting forms.</li>';
        $output .= '  <li>Site Studio API integration.</li>';
        $output .= '</ul></p>';
        $output .= '<p><a href="https://docs.acquia.com/" target="_blank">https://docs.acquia.com/</a></p>';
        return $output;

      default:
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'cohesion_website_settings' => [
        'template' => 'cohesion_website_settings',
        'render element' => 'children',
      ],
    ];
  }

}
