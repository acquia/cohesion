<?php

namespace Drupal\cohesion\Hook;

use Drupal\cohesion\CohesionLayoutRevisionManager;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\SettingsEndpointUtils;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Asset\AssetResolverInterface;
use Drupal\Core\Asset\AttachedAssets;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for Cohesion.
 */
class CohesionHooks {

  public function __construct(
    protected ConfigFactoryInterface $config_factory,
    protected ThemeManagerInterface $theme_manager,
    protected CohesionUtils $cohesion_utils,
    protected EntityTypeManagerInterface $entity_type_manager,
    protected SettingsEndpointUtils $settings_endpoint_utils,
    protected AccountInterface $current_user,
    protected CurrentPathStack $current_path,
    protected AssetResolverInterface $asset_resolver,
    protected FileUrlGeneratorInterface $file_url_generator,
    protected RouteMatchInterface $route_match,
    protected RequestStack $request_stack,
    protected MessengerInterface $messenger,
    protected LanguageManagerInterface $language_manager,
    protected readonly KeyValueFactoryInterface $key_value,
    protected ThemeHandlerInterface $theme_handler,
    protected FileSystemInterface $file_system,
    protected LoggerChannelFactoryInterface $logger_channel_factory,
    protected ModuleHandlerInterface $module_handler,
    protected CohesionLayoutRevisionManager $layout_revision_manager,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name) {
    if ($route_name == 'help.page.cohesion') {
      $output = '<h3>' . t('About') . '</h3>';
      $output .= '<p>' . t('This module defines the base Site Studio entities, import and administration controllers and drush commands.') . '</p>';
      $output .= '<p><ul>';
      $output .= '  <li>Administration menu to set up Site Studio and import assets.</li>';
      $output .= '  <li>Site Studio text format and CKEditor plugins.</li>';
      $output .= '  <li>Drush commands to set up, import and rebuild Site Studio config entities.</li>';
      $output .= '  <li>Google map API settings page controller.</li>';
      $output .= '  <li>Site Studio views formatter plugin.</li>';
      $output .= '  <li>Dynamic library management on the front end.</li>';
      $output .= '  <li>Template suggestions on the front end.</li>';
      $output .= '</ul></p>';
      $output .= '<p><a href="https://docs.acquia.com/drupal-starter-kits/add-ons/site-studio/docs">https://docs.acquia.com/drupal-starter-kits/add-ons/site-studio/docs/</a></p>';

      return $output;
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for html().
   */
  #[Hook('preprocess_html')]
  public function preprocess_html(&$variables): void {
    $current_theme = $this->theme_manager->getActiveTheme();
    $is_admin = $this->config_factory->get('system.theme')
      ->get('admin') == $current_theme->getName();
    $current_request = $this->request_stack->getCurrentRequest();

    // Check for blanked out admin page.
    if ($current_request && $current_request->query->get('coh_clean_page') === 'true') {
      // Remove the admin toolbar and all regions except 'content'.
      $variables['page_top'] = [];
      $variables['page'] = array_filter($variables['page'], fn($region, $key) => $key === 'content' || str_contains($key, '#'), ARRAY_FILTER_USE_BOTH);

      // Attach the clean page library.
      $variables['#attached']['library'][] = 'cohesion/coh-clean-page';
    }

    if ($is_admin) {
      // Display warning message when 'Use Site Studio' is disabled.
      if (!($this->cohesion_utils
        ->usedx8Status()) && (str_contains($this->current_path
        ->getPath(), 'cohesion')) && $this->route_match
        ->getRouteName() !== 'cohesion.configuration.account_settings') {
        $this->messenger->addWarning(t('You cannot  access this page because Site Studio is disabled.'));
      }

    }
    else {
      // Add browser-specific classes to non-admin pages.
      $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';

      $browser_classes = [
        '/msie 9./i' => 'coh-ie9',
        '/msie 10./i' => 'coh-ie10',
        '/Trident\/7.0/' => 'coh-ie11',
      ];

      foreach ($browser_classes as $pattern => $class) {
        if (preg_match($pattern, $ua)) {
          $variables['attributes']['class'][] = $class;
        }
      }
    }
  }

  /**
   * @param $attachments
   * @param $is_dx8_enabled_theme
   * @param $is_admin
   * @return void
   */
  private function attach_libraries(&$attachments, $is_dx8_enabled_theme, $is_admin): void {
    // Attach the reset.css and other CSS.
    if ($is_dx8_enabled_theme && !$is_admin) {
      $attachments['#attached']['library'][] = 'cohesion/coh-theme';
      $attachments['#attached']['library'][] = 'cohesion/coh-module';
      $attachments['#attached']['library'][] = 'cohesion/coh_responsive_grid';
    }

    // Load Site Studio toolbar icon if the user is logged in plus some
    // ui fixes.
    if ($this->current_user->isAuthenticated()) {
      $attachments['#attached']['library'][] = 'cohesion/cohesion-ui';
    }

    // Add Site Studio libraries to Template, Custom styles list pages
    $route_name = $this->route_match->getRouteName();
    $allowed_pages = [
      'entity.cohesion_master_templates.collection',
      'entity.cohesion_content_templates.collection',
      'entity.cohesion_custom_style.collection',
      'entity.cohesion_component.collection',
    ];
    if (in_array($route_name, $allowed_pages)) {
      $attachments['#attached']['library'][] = 'cohesion/cohesion-admin-styles';
    }

    if ($is_admin) {
      // Load icon library for admin pages.
      $icon_lib_path = COHESION_CSS_PATH . '/cohesion-icon-libraries.css';
      if (file_exists($icon_lib_path)) {
        $attachments['#attached']['library'][] = 'cohesion/admin-icon-library';
      }

      $responsive_grid = COHESION_CSS_PATH . '/cohesion-responsive-grid.css';
      if (file_exists($responsive_grid)) {
        $attachments['#attached']['library'][] = 'cohesion/admin-grid-settings';
      }
    }
  }

  /**
   * @param $is_admin
   * @return void
   */
  private function handle_image_browser_warning($is_admin): void {
    // Check the image browser has been set up.
    $current_path = $this->current_path->getPath();
    $image_browser = $this->config_factory
      ->getEditable('cohesion.settings')
      ->get('image_browser');

    if ($is_admin && (!isset($image_browser['config']) || !isset($image_browser['content'])) && str_contains($current_path, 'cohesion')) {

      $args = [
        '@link' => Link::createFromRoute('Click here to configure the image browser settings.', 'cohesion.configuration.system_settings')->toString(),
      ];

      $this->messenger->addWarning(t('No image browsers have been defined for Site Studio. @link', $args));
    }
  }

  #[Hook('page_attachments')]
  public function page_attachments(&$attachments): void {
    $is_admin = $this->cohesion_utils->isAdminTheme();
    $is_dx8_enabled_theme = $this->cohesion_utils->currentThemeUseCohesion();

    // Attach libraries based on theme and page type.
    $this->attach_libraries($attachments, $is_dx8_enabled_theme, $is_admin);

    if ($is_admin) {
      $assets = new AttachedAssets();
      $assets->setLibraries(['cohesion/coh-theme']);
      /** @var \Drupal\Core\Asset\AssetResolver $asset_resolver */
      $css = $this->asset_resolver->getCssAssets($assets, FALSE);

      $css_urls = [];
      foreach ($css as $css_entry) {
        $css_urls[] = $this->file_url_generator->generateAbsoluteString($css_entry['data']);
      }

      $attachments['#attached']['drupalSettings']['cohesion']['themeCSS'] = $css_urls;
    }

    // Add current admin theme.
    $admin_theme = $this->config_factory->get('system.theme')->get('admin');
    $attachments['#attached']['drupalSettings']['cohesion']['currentAdminTheme'] = $admin_theme;

    // Add the front-end settings to drupalSettings so React & other libraries
    // can toggle things.
    $attachments['#attached']['drupalSettings']['cohesion']['front_end_settings'] = [
      'global_js' => $this->config_factory->get('cohesion.frontend.settings')->get('js'),
    ];

    $attachments['#cache']['tags'][] = 'config:cohesion.settings';

    // Add config to Drupal.settings for use in JS.
    $drupal_settings_js = [
      'google_map_api_key',
      'google_map_api_key_geo',
      'animate_on_view_mobile',
      'add_animation_classes',
    ];

    foreach ($drupal_settings_js as $setting) {
      $value = $this->config_factory->get('cohesion.settings')->get($setting);
      if ($value !== NULL) {
        $attachments['#attached']['drupalSettings']['cohesion'][$setting] = $value;
      }
    }

    // Add responsive grid settings for use in JS.
    try {
      /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity */
      $entity = $this->entity_type_manager
        ->getStorage('cohesion_website_settings')
        ->load('responsive_grid_settings');

      if ($entity) {
        $attachments['#attached']['drupalSettings']['cohesion']['responsive_grid_settings'] = $entity->getDecodedJsonValues();
      }
    } catch (PluginNotFoundException $e) {
      throw new PluginNotFoundException($e->getMessage(), $e->getCode(), $e);
    }

    // Add default font settings for use in JS.
    try {
      /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $defaultFontSettings */
      $defaultFontSettings = $this->entity_type_manager
        ->getStorage('cohesion_website_settings')
        ->load('default_font_settings');

      if ($entity) {
        $attachments['#attached']['drupalSettings']['cohesion']['default_font_settings'] = $defaultFontSettings->getDecodedJsonValues();
      }
    } catch (PluginNotFoundException $e) {
      throw new PluginNotFoundException($e->getMessage(), $e->getCode(), $e);
    }

    // Attach the font and icon libraries to all pages.
    $libraries_callback = function ($value) use (&$attachments) {
      if ($value) {
        $lib = ['rel' => 'stylesheet', 'href' => $value, 'type' => 'text/css'];
        $attachments['#attached']['html_head_link'][] = [$lib];
      }
    };

    // Add to drupalSettings
    if ($font_libraries = $this->settings_endpoint_utils
      ->siteLibraries('font_libraries')) {
      array_walk($font_libraries, $libraries_callback);
    }

    if ($icon_libraries = $this->settings_endpoint_utils
      ->siteLibraries('icon_libraries')) {
      array_walk($icon_libraries, $libraries_callback);
    }

    // Use Site Studio
    $attachments['#attached']['drupalSettings']['cohesion']['use_dx8'] = $this->cohesion_utils->usedx8Status();

    // View style.
    $attachments['#attached']['drupalSettings']['cohesion']['sidebar_view_style'] =
      $this->config_factory->get('cohesion.settings')->get('sidebar_view_style') ?? 'titles';

    // Log Site Studio error with default logging enabled unless
    // explicitly disabled.
    $log_dx8_error = $this->config_factory->get('cohesion.settings')->get('log_dx8_error') ?? 'enable';
    $attachments['#attached']['drupalSettings']['cohesion']['log_dx8_error'] = $log_dx8_error !== 'disable';

    // Site Studio JS error log endpoint
    $language_none = $this->language_manager
      ->getLanguage(LanguageInterface::LANGCODE_NOT_APPLICABLE);
    $attachments['#attached']['drupalSettings']['cohesion']['error_url'] = Url::fromRoute('cohesion.error_logger') ? Url::fromRoute('cohesion.error_logger', [], ['language' => $language_none])
      ->toString() : NULL;
    // Site Studio content path lookup table
    $dx8_content_paths = $this->key_value->get('cohesion.assets.static_assets')
      ->get('dx8_content_paths');
    $attachments['#attached']['drupalSettings']['cohesion']['dx8_content_paths'] = $dx8_content_paths['items'] ?? [];

    // Add a warning message if the image browser has not been configured.
    $this->handle_image_browser_warning($is_admin);
  }

  #[Hook('editor_js_settings_alter')]
  public function editor_js_settings($settings): void {
    $route_name = $this->route_match->getRouteName();

    if (isset($settings['editor']['formats']['cohesion']) && !str_contains($route_name, 'entity.cohesion_custom_style.')) {
      $settings['editor']['formats']['cohesion']['editorSettings']['bodyClass'] = 'coh-wysiwyg';
    }
  }

  /**
   * Implements template_preprocess_views_view()
   */
  #[Hook('preprocess_views_view')]
  public function preprocess_views_view(&$variables): void {
    $view = $variables['view'];
    $cohesion_views = [
      'custom_styles',
      'cohesion_components_admin',
      'cohesion_master_templates_list',
    ];
    $id = $view->storage->id();
    if (in_array($id, $cohesion_views)) {
      $variables['attributes']['ng-controller'] = 'CohFormRendererCtrl';
    }

    // Give the template the current page from the pager (if available).
    $variables['current_page'] = 1;

    if (isset($view->pager)) {
      $variables['current_page'] = $view->pager->getCurrentPage() + 1;
    }
  }

  /**
   * Implements hook_menu_alter().
   */
  #[Hook('link_alter')]
  public function link_alter(&$variables) {
    // Hide cohesion navigation menu items until assets are imported.
    /** @var \Drupal\Core\Url $url */
    $url = $variables['url'];
    $config = $this->config_factory->get('cohesion.settings');
    if ($url->isExternal() || !$url->isRouted() || $config->get('asset_is_imported')) {
      return;
    }

    $cohesion_routes = $this->cohesion_utils->getCohesionRoutes();
    if (!in_array($url->getRouteName(), array_keys($cohesion_routes))) {
      return;
    }
    else {
      $variables['options']['attributes']['class'][] = 'visually-hidden';
      $current_path = $this->current_path->getPath();
      if (str_contains($current_path, 'cohesion')) {
        $this->messenger->addWarning(t('Please import Site Studio assets.'));
      }
    }
  }

  /**
   * Implements hook_cron().
   *
   * Cleanup of orphaned cohesion layout revisions.
   */
  #[Hook('cron')]
  public function cron() {
    try {
      $this->layout_revision_manager->processCronCleanup();
    }
    catch (\Exception $e) {
      $this->logger_channel_factory->get('cohesion')->error(
        'Error during cron cleanup: @error',
        [
          '@error' => $e->getMessage(),
        ],
      );
    }
  }

  /**
   * Implements hook_field_info_alter().
   */
  #[Hook('field_info_alter')]
  public function field_info_alter(&$info) {
    if (isset($info['link']['class'])) {
      $info['link']['class'] = 'Drupal\cohesion\Plugin\Field\FieldType\CohesionLinkItem';
    }

    if ($this->module_handler->moduleExists('tmgmt') && isset($info['string_long'])) {
      $info['string_long']['tmgmt_field_processor'] = 'Drupal\cohesion\CohesionLayoutFieldProcessor';
    }
  }

  /**
   * @param $form
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   * @param $form_id
   */
  #[Hook('form_system_theme_settings_alter')]
  public function form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
    // Get the theme id from the theme settings being edited.
    $build_info = $form_state->getBuildInfo();
    $args = $build_info['args'];

    if (isset($args[0])) {

      $theme_id = $args[0];

      if ($this->cohesion_utils
        ->themeHasCohesionEnabled($theme_id)
      ) {
        $form['cohesion_settings'] = [
          '#type' => 'details',
          '#title' => t('Site Studio'),
          '#open' => TRUE,
          'toggle_cohesion_build_assets' => [
            '#type' => 'checkbox',
            '#title' => t('Build Site Studio assets'),
            '#disabled' => $this->theme_handler->getDefault() == $theme_id,
            '#default_value' => (\Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider')->getSetting('features.cohesion_build_assets', $theme_id) || $this->theme_handler->getDefault() == $theme_id),
          ],
        ];
      } else {
        $form['cohesion'] = [
          '#type' => 'details',
          '#title' => t('Site Studio'),
          '#open' => TRUE,
          'toggle_layout_canvas_field' => [
            '#type' => 'checkbox',
            '#title' => t('Generate templates only.'),
            '#description' => t('This setting prevents Site Studio from generating CSS styles for this theme. This is required for AMP themes.'),
            '#default_value' => \Drupal::service('Drupal\Core\Extension\ThemeSettingsProvider')->getSetting('features.layout_canvas_field', $theme_id),
          ],
        ];
      }
    }
  }

  /**
   * Implements hook_config_schema_info_alter().
   */
  #[Hook('config_schema_info_alter')]
  public function config_schema_info_alter(&$definitions) {
    if (isset($definitions['theme_settings']['mapping']['features']['mapping']) && is_array($definitions['theme_settings']['mapping']['features']['mapping'])) {
      $definitions['theme_settings']['mapping']['features']['mapping']['cohesion_build_assets'] = [
        'type' => 'boolean',
        'label' => 'Build site studio assets',
      ];

      $definitions['theme_settings']['mapping']['features']['mapping']['layout_canvas_field'] = [
        'type' => 'boolean',
        'label' => 'Build site studio assets',
      ];
    }
  }

  /**
   * Implements hook_themes_uninstalled().
   */
  #[Hook('themes_uninstalled')]
  public function themes_uninstalled(array $themes) {
    // Upon uninstall of a theme with cohesion enabled remove all cohesion
    // stylesheets.
    foreach ($themes as $theme) {
      if ($this->cohesion_utils->themeHasCohesionEnabled($theme)) {
        foreach (['base', 'prefixed', 'theme', 'json', 'preview'] as $type) {
          $theme_file = \Drupal::service('cohesion.local_files_manager')
            ->getStyleSheetFilename($type, $theme);
          $this->file_system->delete($theme_file);
        }
      }
    }
  }

  /**
   * Implements hook_system_info_alter().
   *
   * Adds prefixed Site Studio CKEditor 5 stylesheet to default and
   * admin themes.
   */
  #[Hook('system_info_alter')]
  public function system_info_alter(array &$info, Extension $file, $type) {
    if ($type === 'theme') {
      $theme_config = $this->config_factory->get('system.theme');
      $default_theme_id = $theme_config->get('default');
      $admin_theme_id = $theme_config->get('admin');

      if (in_array($file->getName(), [$admin_theme_id, $default_theme_id])) {
        $wysiwyg_cache_token = $this->key_value->get('cohesion.wysiwyg_cache_token');
        $wysiwyg_cache_buster = $wysiwyg_cache_token->get('cache_token') ? '?_t=' . $wysiwyg_cache_token->get('cache_token') : '';
        $prefixed_ckeditor_css = \Drupal::service('cohesion.local_files_manager')
          ->getStyleSheetFilename('prefixed', $file->getName(), TRUE);
        if (!isset($info['ckeditor5-stylesheets']) || $info['ckeditor5-stylesheets'] === FALSE) {
          $info['ckeditor5-stylesheets'] = [];
        }
        $absolute_url = $this->file_url_generator->generateAbsoluteString($prefixed_ckeditor_css) . $wysiwyg_cache_buster;
        $info['ckeditor5-stylesheets'][] = $this->file_url_generator->transformRelative($absolute_url);
      }

    }
  }

  /**
   * Implements hook_field_widget_complete_WIDGET_TYPE_form_alter() for text_textarea_with_summary.
   * Adds the ssa-ck-content class so that the styles can be "previewed".
   */
  #[Hook('field_widget_complete_text_textarea_with_summary_form_alter')]
  public function field_widget_complete_text_textarea_with_summary_form_alter(&$field_widget_complete_form) {
    $field_widget_complete_form['#attributes']['class'][] = 'ssa-ck-content';
  }

  /**
   * Implements hook_field_widget_complete_WIDGET_TYPE_form_alter() for text_textarea_with_summary.
   * Adds the ssa-ck-content class so that the styles can be "previewed".
   */
  #[Hook('field_widget_complete_text_textarea_form_alter')]
  public function field_widget_complete_text_textarea_form_alter(&$field_widget_complete_form) {
    $field_widget_complete_form['#attributes']['class'][] = 'ssa-ck-content';
  }

}
