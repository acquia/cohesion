<?php

namespace Drupal\cohesion\Hook;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\Services\LocalFilesManager;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\StreamWrapper\PublicStream;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Hook implementations for theme alterations and template suggestions.
 */
class CohesionThemeHooks {

  public function __construct(
    protected EntityTypeManagerInterface $entity_type_manager,
    protected ThemeManagerInterface $theme_manager,
    protected CohesionUtils $cohesion_utils,
    protected LocalFilesManager $local_files_manager,
    protected StreamWrapperManagerInterface $stream_wrapper_manager,
    protected ConfigFactoryInterface $config_factory,
    protected FileSystemInterface $file_system,
    protected ModuleExtensionList $extension_list_module,
    protected LoggerChannelFactoryInterface $logger_channel_factory,
  ) {}

  /**
   * @param $libraries
   * @param $gridSettings
   * @return void
   */
  private function update_breakpoints(&$libraries, $gridSettings): void {
    foreach ($libraries as $key => $library) {
      if (!str_starts_with($key, 'coh_element_')) {
        continue;
      }

      foreach ($library['css']['component'] as $componentFile => $file) {
        if (empty($file['media'])) {
          continue;
        }

        foreach ($gridSettings as $breakpoint) {
          if ($breakpoint->key === $file['media']) {
            $libraries[$key]['css']['component'][$componentFile] = [
              'media' => $breakpoint->mediaQuery,
              'weight' => $breakpoint->weight,
            ];
          }
        }
      }
    }
  }

  /**
   * @param $libraries
   * @param $styles
   * @param $styleTypes
   * @param $processAsSeparateLibs
   * @return void
   */
  private function handle_custom_styles(&$libraries, $styles, $styleTypes, $processAsSeparateLibs): void {
    foreach ($styleTypes as $sectionName => $sectionKeys) {
      $cssValues = array_intersect_key($styles, array_flip($sectionKeys));

      foreach ($cssValues as $entryName => $css) {
        if (!in_array($entryName, $processAsSeparateLibs)) {
          continue;
        }

        foreach ($css as $name => $value) {
          if (!$value) {
            continue;
          }

          $cssFileName = $entryName === 'cohesion_custom_style'
            ? "{$entryName}-{$name}.css"
            : "custom-element-styles-{$name}.css";

          $fullPathDestination = COHESION_CSS_PATH . "/{$sectionName}/" . str_replace('_', '-', $cssFileName);

          if ($entryName === 'cohesion_custom_style') {
            $name = substr($name, 0, strpos($name, '_'));
            $libraries['coh_custom_style_' . $name]['css']['component'][$fullPathDestination] = ['weight' => -9];
          } else {
            $libraries[$name]['css']['component'][$fullPathDestination] = ['weight' => -9];
          }
        }
      }
    }
  }

  /**
   * @param $libraries
   * @param $gridSettings
   * @return void
   */
  private function add_responsive_grid_libraries(&$libraries, $gridSettings): void {
    foreach ($gridSettings as $breakpoint) {
      $cssFileName = 'responsive-grid-settings-' . $breakpoint->key . '.css';
      $fullPathDestination = 'css/dist/' . str_replace('_', '-', 'responsive-grid-settings') . "/" . str_replace('_', '-', $cssFileName);
      $libraries['coh_responsive_grid']['css']['theme'][$fullPathDestination] = [
        'media' => $breakpoint->mediaQuery,
        'weight' => $breakpoint->weight,
      ];
      $libraries['admin-responsive-grid-settings']['css']['theme'][$fullPathDestination] = [
        'media' => $breakpoint->mediaQuery,
        'weight' => $breakpoint->weight,
      ];
    }
    $libraries['coh_responsive_grid']['css']['theme']['css/dist/responsive-grid-settings/responsive-grid-settings.css'] = [
      'weight' => 0,
    ];
  }

  /**
   * Build cohesion libraries (base and theme styles).
   *
   * @param $libraries
   * @param $extension
   */
  #[Hook('library_info_alter')]
  public function library_info_alter(&$libraries, $extension): void {
    if ($extension !== 'cohesion') {
      return;
    }

    $libraries = Json::decode(str_replace(
      'public:\/\/cohesion\/',
      '/' . PublicStream::basePath() . '/cohesion/',
      Json::encode($libraries)
    ));

    if (!$this->cohesion_utils->currentThemeUseCohesion() && !$this->cohesion_utils->isAdminTheme()) {
      return;
    }

    $customStylesLoad = $this->cohesion_utils->loadCustomStylesOnPageOnly();
    $processAsSeparateLibs = $this->cohesion_utils->styleTypesSeparateLibraries();

    $styleTypes = [
      'base' => [
        'cohesion_website_settings',
        'cohesion_base_styles',
        'default_element_styles',
      ],
      'theme' => [
        'cohesion_custom_style',
        'custom_element_styles',
        'other_styles',
      ],
    ];

    $themeId = $this->theme_manager->getActiveTheme()->getName();
    $cssJson = $this->local_files_manager->getStyleSheetJson($themeId);
    $decodedCss = Json::decode($cssJson);

    $gridSettings = $this->config_factory->getEditable('cohesion.settings')->get('media_queries');
    $gridSettings = $gridSettings ? json_decode($gridSettings) : NULL;

    if ($gridSettings) {
      $this->update_breakpoints($libraries, $gridSettings);
    }

    if (isset($decodedCss['styles'])) {
      $this->handle_custom_styles($libraries, $decodedCss['styles'], $styleTypes, $processAsSeparateLibs);
    }

    if ($gridSettings) {
      $this->add_responsive_grid_libraries($libraries, $gridSettings);
    }

    if ($customStylesLoad) {
      $previewCSS = $this->local_files_manager->getStyleSheetFilename('preview');
      $libraries['coh-preview']['css']['theme'][$previewCSS] = ['weight' => 0];
    }

    // Inject Google Maps API key into the appropriate libraries.
    if ($google_map_api_key = $this->config_factory->get('cohesion.settings')->get('google_map_api_key')) {
      $libraries_to_update = [
        'element_templates.google-map',
        'coh_element_google_map',
      ];

      foreach ($libraries_to_update as $library_name) {
        if (isset($libraries[$library_name])) {
          $libraries[$library_name]['js']["https://maps.googleapis.com/maps/api/js?key={$google_map_api_key}"] = [
            'type' => 'external',
            'minified' => TRUE,
          ];
        }
      }
    }
  }

  /**
   * Implements hook_css_alter().
   *
   * Alter CSS per theme for Site Studio base and theme CSS
   */
  #[Hook('css_alter')]
  public function css_alter(&$css): void {
    $module_path = $this->extension_list_module->getPath('cohesion');
    $base_default_css = $module_path . '/css/base-default.css';
    $theme_default_css = $module_path . '/css/theme-default.css';

    $cohesion_css = [
      'base-default' => $base_default_css,
      'reset' => $module_path . '/css/reset.css',
      'theme-default' => $theme_default_css,
    ];

    if (!empty(array_intersect(array_keys($css), $cohesion_css))) {
      // Get the smallest weight set on all CSS libraries
      // We need to set the reset.css and the base stylesheets as the
      // first two stylesheet loaded on the head.
      $min_weight = CSS_BASE;
      // Find the current maximum weight.
      $max_weight = CSS_BASE;
      foreach ($css as $key => $css_definition) {
        if (isset($css_definition['weight']) && $css_definition['weight'] < $min_weight) {
          $min_weight = $css_definition['weight'];
        }

        if (isset($css_definition['weight']) && $css_definition['weight'] > $max_weight) {
          $max_weight = $css_definition['weight'];
        }

        // Only override weight for element CSS without a breakpoint media
        // query breakpoint CSS must keep its API-assigned weight for
        // correct order.
        if (str_starts_with($key, $module_path . '/css/dist/elements')) {
          $media = $css_definition['media'] ?? '';
          if (empty($media) || $media === 'all') {
            $css[$key]['weight'] = $min_weight;
          }
        }
      }

      $is_admin = $this->cohesion_utils->isAdminTheme();

      // Site studio cannot be used in admin theme, so when attached from
      // an admin theme, get the default theme and check if it has site studio
      if ($is_admin) {
        $active_theme_id = $this->config_factory->get('system.theme')->get('default');
        if (!$this->cohesion_utils->themeHasCohesionEnabled($active_theme_id)) {
          return;
        }
      } else {
        $active_theme_id = $this->theme_manager
          ->getActiveTheme()
          ->getName();
      }

      if (isset($css[$cohesion_css['base-default']])) {
        $css_filename = $this->local_files_manager
          ->getStyleSheetFilename('base', $active_theme_id, TRUE);
        $css[$base_default_css]['data'] = $css_filename;
        // Set the base stylesheet before the first CSS
        $min_weight--;
        $css[$base_default_css]['weight'] = $min_weight;
      }

      if (isset($css[$cohesion_css['reset']])) {
        // Set the reset.css as the first CSS to load.
        $min_weight--;
        $css[$module_path . '/css/reset.css']['weight'] = $min_weight;
      }

      if (isset($css[$cohesion_css['theme-default']])) {
        $css_filename = $this->local_files_manager
          ->getStyleSheetFilename('theme', $active_theme_id, TRUE);
        $css[$theme_default_css]['data'] = $css_filename;
        // Set the theme CSS to be loaded last.
        $css[$theme_default_css]['weight'] = $max_weight + 1;
      }
    }
  }

  /**
   * Implements hook_theme_registry_alter().
   *
   * Allow loading of theme templates from the Site Studio template store.
   */
  #[Hook('theme_registry_alter')]
  public function theme_registry_alter(array &$theme_registry): void {
    // Get real path to templates and extract relative path for theme hooks.
    // Note: The theme registry expects template paths relative to DRUPAL_ROOT.
    if ($wrapper = $this->stream_wrapper_manager
      ->getViaUri(COHESION_TEMPLATE_PATH)) {
      $template_path = $wrapper->basePath() . '/cohesion/templates';
    }
    else {
      // Do nothing if template path is not valid.
      $this->logger_channel_factory
        ->error(t('Unable to get stream wrapper for Site Studio templates path: @uri', [
          '@uri' => COHESION_TEMPLATE_PATH,
        ]));
      return;
    }
    // Scan for template files and override their location in the theme
    // registry.
    $template_files = \Drupal::service('cohesion.template_storage')->listAll();

    foreach ($template_files as $file) {
      $template = basename($file, '.html.twig');
      $theme_hook = str_replace('-', '_', $template);

      [$base_theme_hook] = explode('__', $theme_hook, 2);

      // Override existing theme hook or duplicate the base hook
      // (if one exists).
      if (isset($theme_registry[$base_theme_hook]) || $base_theme_hook === 'component') {
        if (isset($theme_registry[$theme_hook]) && $theme_registry[$theme_hook]) {
          $theme_registry[$theme_hook]['path'] = $template_path;
        }
        else {
          // And entry to the theme registry.
          $theme_info = $theme_registry[$base_theme_hook] ?? [];
          $theme_info['template'] = str_replace('_', '-', $theme_hook);
          $theme_info['path'] = $template_path;
          $theme_info['base hook'] = 'component';
          $theme_registry[$theme_hook] = $theme_info;
        }
      }
    }
  }

  /**
   * Suggest the cohesion view template specific to this view.
   *
   * @param array $variables
   *   Theme variables.
   *
   * @return array
   *   Return template suggestions.
   */
  #[Hook('theme_suggestions_views_view')]
  public function theme_suggestions_views_view(array $variables): array {
    $suggestions = [];

    if ($variables['view']->style_plugin->getPluginId() == 'cohesion_layout' &&
      $view_template_id = $variables['view']->style_plugin->options['views_template']
    ) {
      $suggestions[] = 'views_view__cohesion_' . $view_template_id;
      $suggestions[] = 'views_view__cohesion_' . $view_template_id . '__' . $this->theme_manager->getActiveTheme()->getName();
    }

    return $suggestions;
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_menu_alter')]
  public function theme_suggestions_menu_alter(array &$suggestions, array $variables): void {
    if (isset($variables['menu_name'])) {
      $menu_name = $variables['menu_name'];
      $is_mobile_menu = strpos($menu_name, 'mobile');
      if (isset($variables['attributes']['block'])) {
        $block = $this->entity_type_manager->getStorage('block')->load($variables['attributes']['block']);
        $region = $block->getRegion();
        $suggestions[] = 'menu__' . $region . '__' . $menu_name;
      }
      // If menu name contains the word mobile, create common template
      // suggestion.
      if ((isset($variables['attributes']['block'])) && ($is_mobile_menu !== FALSE)) {
        $suggestions[] = 'menu__' . $region . '__mobile-menus';
      }

      if (isset($variables['theme_hook_original']) && strpos($variables['theme_hook_original'], 'menu__cohesion') == 0) {
        $suggestions[] = $variables['theme_hook_original'] . '__' . $this->theme_manager->getActiveTheme()->getName();
      }

      $suggestions[] = 'menu__cohesion_test';
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'cohesion_view' => [
        'render element' => 'elements',
        'base hook' => 'views_view',
      ],
    ];
  }

  /**
   * Preprocess the component preview iframe page.html.twig
   * See: templates/page--cohesionapi--component--preview.html.twig.
   *
   * @param $variables
   */
  #[Hook('preprocess_cohesion_preview_page')]
  public function preprocess_cohesion_preview_page(&$variables): void {
    // Load the build created in CohesionComponentController::preview.
    $variables['preview_build'] = &drupal_static('component_preview_build');
  }

}
