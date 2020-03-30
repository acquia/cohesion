<?php

namespace Drupal\cohesion\Services;

use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Class CohesionUtils.
 *
 * @package Drupal\cohesion
 */
class CohesionUtils {

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * CohesionUtils constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ThemeManagerInterface $theme_manager) {
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
  }

  /**
   * @return bool
   */
  public function isAdminTheme() {
    return \Drupal::config('system.theme')
      ->get('admin') == $this->themeManager->getActiveTheme()->getName();
  }

  /**
   * Whether the current theme had cohesion enabled.
   *
   * @return bool - Returns TRUE if the current theme or one of its parent has
   *   cohesion enabled (cohesion: true in info.yml)
   */
  public function currentThemeUseCohesion() {
    return $this->themeHasCohesionEnabled(NULL);
  }

  /**
   * Given the theme info of a theme, is it cohesion enabled.
   *
   * @param $theme_info
   *
   * @return bool
   */
  private function isThemeCohesionEnabled($theme_info) {
    return property_exists($theme_info, 'info') && is_array($theme_info->info) && isset($theme_info->info['cohesion']) && $theme_info->info['cohesion'] === TRUE;
  }

  /**
   * Get all enabled theme with Cohesion enabled.
   *
   * @return \Drupal\Core\Extension\Extension[] - Array of theme info
   */
  public function getCohesionEnabledThemes() {
    $themes = [];
    foreach ($this->themeHandler->listInfo() as $theme_info) {
      if ($this->themeHasCohesionEnabled($theme_info->getName())) {
        $themes[] = $theme_info;
      }
    }
    return $themes;
  }

  /**
   * Returns whether a theme has cohesion enabled, it can be its parent(s)
   *
   * @param $theme
   *
   * @return bool
   */
  public function themeHasCohesionEnabled($theme_id = NULL) {

    if (is_null($theme_id) || !isset($this->themeHandler->listInfo()[$theme_id])) {
      $theme_extension = $this->themeManager->getActiveTheme()->getExtension();
    }
    else {
      $theme_extension = $this->themeHandler->listInfo()[$theme_id];
    }

    if ($this->isThemeCohesionEnabled($theme_extension)) {
      return TRUE;
    }
    elseif (property_exists($theme_extension, 'base_themes') && is_array($theme_extension->base_themes)) {
      foreach ($theme_extension->base_themes as $theme_id => $theme_name) {
        if (isset($this->themeHandler->listInfo()[$theme_id]) && $this->isThemeCohesionEnabled($this->themeHandler->listInfo()[$theme_id])) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   * @return array
   */
  public function getCohesionRoutes() {
    $query = \Drupal::database();
    $routes_results = $query->select('router', 'r')
      ->fields('r', ['name'])
      ->condition('name', '%cohesion%', 'LIKE')
      ->execute()
      ->fetchCol();

    $routes = array_filter($routes_results, function ($route) {
      return (!in_array($route, [
        'cohesion.settings',
        'cohesion.configuration',
        'cohesion.configuration.account_settings',
        'cohesion.configuration.batch',
      ]));
    });
    return $routes ? \Drupal::service('router.route_provider')
      ->getRoutesByNames($routes) : [];
  }

  /**
   * @return bool
   * @todo - store as a static.
   */
  public function usedx8Status() {
    $dx8_config = \Drupal::config('cohesion.settings');
    if (!$dx8_config || $dx8_config->get('use_dx8') === 'disable' || !$dx8_config->get('api_key') || $dx8_config->get('api_key') == '') {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Format the tokens for the API.
   *
   * @param $value
   */
  public function processTokenForApi(&$value) {
    if (is_string($value)) {
      $token_service = \Drupal::token();

      $token_info = $token_service->getInfo();

      if ($found_tokens = $token_service->scan($value)) {
        foreach ($found_tokens as $context => $token_group) {
          if (in_array($context, array_keys($token_info['types']))) {
            foreach ($token_group as $token) {
              $context_variable = str_replace('-', '_', $context);

              \Drupal::moduleHandler()->alter('dx8_' . $context . '_drupal_token_context', $context_variable);

              // If token has been detected replace potential breaking chars with nothing as they are not valid.
              $context = str_replace(['[', ']', '{', '}'], '', $context);

              $twig_token = '[token.' . str_replace([
                '[',
                ']',
                '{',
                '}',
              ], '', $token) . '|' . $context . '|' . $context_variable . ']';
              $value = str_replace($token, $twig_token, $value);
            }
          }
        }
      }
    }
  }

}
