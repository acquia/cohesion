<?php

namespace Drupal\cohesion_custom_styles\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for Cohesion custom styles.
 */
class CohesionCustomStylesHooks {

  public function __construct(
    protected RequestStack $request_stack,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name) {
    switch ($route_name) {
      // Main module help for the cohesion_custom_styles module.
      case 'help.page.cohesion_custom_styles':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('This module defines the custom style config entities for creating custom CSS class styles.') . '</p>';
        $output .= '<p><ul>';
        $output .= '  <li>Custom style configuration entity and supporting forms.</li>';
        $output .= '  <li>Site Studio API integration.</li>';
        $output .= '</ul></p>';
        $output .= '<p><a href="https://docs.acquia.com/" target="_blank">https://docs.acquia.com/</a></p>';
        return $output;

      default:
    }
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function page_attachments(array &$attachments): void {
    // Set active custom style accordion group.
    if (($active_group = $this->request_stack->getCurrentRequest()->get('active_group'))) {
      $attachments['#attached']['drupalSettings']['cohesion']['activeCustomStyleGroup'] = $active_group;
    }
  }

  /**
   * Implements hook_menu_local_actions_alter().
   */
  #[Hook('menu_local_actions_alter')]
  public function menu_local_actions_alter(&$local_actions): void {
    // Add class to custom styles admin link actions.
    if (isset($local_actions['cohesion_custom_style.toggle_style_groups'])) {
      $local_actions['cohesion_custom_style.toggle_style_groups']['options']['attributes']['class'][] = 'coh-toggle-accordion';
      $local_actions['cohesion_custom_style.toggle_style_groups']['options']['attributes']['class'][] = 'open';
      $local_actions['cohesion_custom_style.toggle_style_groups']['options']['attributes']['role'][] = 'button';
    }
  }

}
