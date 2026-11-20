<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Attaches admin theme design tokens to the media library AJAX dialog.
 *
 * The dialog's own component CSS (e.g. default_admin/media_library) depends
 * on design-token custom properties (--admin-color-*, --admin-space-*, etc.)
 * that are normally only attached to a request via
 * \Drupal\Core\Render\MainContent\HtmlRenderer::prepare() invoking
 * hook_page_attachments(), which only runs for the full HTML page wrapper.
 * The media library dialog is rendered through the AJAX wrapper instead, so
 * even once \Drupal\cohesion\Theme\CohesionMediaLibraryThemeNegotiator forces
 * the admin theme to be active, those tokens are never requested.
 *
 * Drupal's AJAX CSS attachment has no concept of DOM-scoping: any library
 * attached here lands in the page's global <head>, not scoped to the dialog.
 * Attaching an admin theme's full library (e.g. default_admin/global-styling)
 * would therefore leak unrelated page-chrome CSS (bare `body`, `html`,
 * `table`, `.panel`, etc. selectors) onto the rest of the front-end page.
 * Instead, this only attaches a curated, per-theme list of files that
 * contain nothing but `:root`-scoped custom property declarations, built
 * for every curated theme (regardless of which theme is active at
 * discovery time) by cohesion_library_info_build(), and selects the
 * theme-specific library (e.g. media_library_theme_variables__default_admin)
 * matching the theme active for this request.
 *
 * @see \Drupal\cohesion\EventSubscriber\CohesionMediaLibraryAttachmentsSubscriber::SAFE_VARIABLE_FILES
 * @see cohesion_library_info_build()
 */
class CohesionMediaLibraryAttachmentsSubscriber implements EventSubscriberInterface {

  /**
   * Admin themes with a curated, safe-to-attach design-token CSS file list.
   *
   * Each file must contain only `:root`-scoped custom property declarations
   * (no bare element/class selectors with real visual declarations), since
   * these are attached globally to the AJAX response with no DOM-scoping.
   *
   * Paths are relative to the theme's own directory.
   */
  const SAFE_VARIABLE_FILES = [
    'claro' => [
      'css/base/variables.css',
    ],
    'default_admin' => [
      'css/_variables/variables-color-primitive.css',
      'css/_variables/variables-color-semantic.css',
      'css/_variables/variables-spacing.css',
      'css/_variables/variables-typography.css',
      'css/_variables/variables-misc.css',
      'css/_variables/variables-components.css',
    ],
  ];

  /**
   * Admin component CSS needed for the media library to match the back end.
   *
   * These are the files the admin theme loads in its global styling that the
   * media library dialog relies on (form controls, buttons, details,
   * messages, pager, etc.). Files with bare element selectors that would
   * restyle the surrounding page (elements.css, typography.css, tables.css,
   * menus-and-lists.css) are deliberately excluded.
   *
   * Paths are relative to the theme's own directory.
   */
  const SAFE_COMPONENT_FILES = [
    'default_admin' => [
      'css/components/button.css',
      'css/components/action-link.css',
      'css/components/container-inline.css',
      'css/components/container-inline.module.css',
      'css/components/details.css',
      'css/components/divider.css',
      'css/components/fieldset.css',
      'css/components/form.css',
      'css/components/form--checkbox-radio.css',
      'css/components/form--checkbox-toggle.css',
      'css/components/form--field-multiple.css',
      'css/components/form--managed-file.css',
      'css/components/form--text.css',
      'css/components/form--select.css',
      'css/components/form-inline.css',
      'css/components/help.css',
      'css/components/image-preview.css',
      'css/components/messages.css',
      'css/components/pager.css',
      'css/components/tabs.css',
    ],
  ];

  /**
   * Per-theme libraries providing the theme's modal and views styling.
   *
   * The admin theme only swaps its dialog CSS in via library overrides on
   * core/drupal.dialog, which has already been loaded by the front-end page
   * (with its own theme) before the media library dialog is opened over AJAX,
   * so the admin theme's modal styling must be attached explicitly.
   */
  const DIALOG_LIBRARIES = [
    'claro' => ['claro/claro.drupal.dialog'],
    'default_admin' => [
      'default_admin/admin.drupal.dialog',
      // Exposed filter form styling; scoped to .views-exposed-form classes.
      'default_admin/views',
    ],
  ];

  /**
   * Constructs a CohesionMediaLibraryAttachmentsSubscriber object.
   *
   * @param \Drupal\Core\Theme\ThemeNegotiatorInterface $themeNegotiator
   *   The cohesion media library theme negotiator.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $themeManager
   *   The theme manager.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    protected ThemeNegotiatorInterface $themeNegotiator,
    protected ThemeManagerInterface $themeManager,
    protected RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Attaches the active theme's design-token library to the render array.
   *
   * @param \Symfony\Component\HttpKernel\Event\ViewEvent $event
   *   The kernel view event.
   */
  public function onView(ViewEvent $event): void {
    $result = $event->getControllerResult();
    if (!is_array($result) || !$this->themeNegotiator->applies($this->routeMatch)) {
      return;
    }

    $theme_name = $this->themeManager->getActiveTheme()->getName();
    if (isset(self::SAFE_VARIABLE_FILES[$theme_name])) {
      $result['#attached']['library'][] = 'cohesion/media_library_theme_variables__' . $theme_name;
      if (isset(self::SAFE_COMPONENT_FILES[$theme_name])) {
        $result['#attached']['library'][] = 'cohesion/media_library_theme_components__' . $theme_name;
      }
      if (isset(self::DIALOG_LIBRARIES[$theme_name])) {
        foreach (self::DIALOG_LIBRARIES[$theme_name] as $library) {
          $result['#attached']['library'][] = $library;
        }
      }
      $event->setControllerResult($result);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    // Run before the default priority (0) main content view subscriber
    // converts the controller result into a response.
    $events[KernelEvents::VIEW][] = ['onView', 5];
    return $events;
  }

}
