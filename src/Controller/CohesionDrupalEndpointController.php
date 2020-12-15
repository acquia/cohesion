<?php

namespace Drupal\cohesion\Controller;

use Drupal\Component\Uuid\Uuid;
use Drupal\entity_browser\Element\EntityBrowserElement;
use Drupal\block\BlockRepositoryInterface;
use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\Entity\EntityAutocompleteMatcher;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Site\Settings;
use Drupal\media_library\MediaLibraryState;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CohesionDrupalEndpointController.
 *
 * Returns Drupal data to Angular (views, blocks, node lists, etc).
 * See function index() for the entry point.
 * See function index() for the entry point.
 *
 * @package Drupal\cohesion\Controller
 */
class CohesionDrupalEndpointController extends ControllerBase {

  /**
   * The autocomplete matcher for entity references.
   *
   * @var \Drupal\Core\Entity\EntityAutocompleteMatcher
   */
  protected $matcher;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * CohesionDrupalEndpointController constructor.
   *
   * @param \Drupal\Core\Entity\EntityAutocompleteMatcher $matcher
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $themeHandler
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   */
  public function __construct(EntityAutocompleteMatcher $matcher, ThemeHandlerInterface $themeHandler, EntityTypeManagerInterface $entityTypeManager, ModuleHandlerInterface $moduleHandler) {
    $this->matcher = $matcher;
    $this->themeHandler = $themeHandler;
    $this->entityTypeManager = $entityTypeManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Create the controller.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   Controller's container.
   *
   * @return static
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cohesion.autocomplete_matcher'),
      $container->get('theme_handler'),
      $container->get('entity_type.manager'),
      $container->get('module_handler')
    );
  }

  /**
   * Return a list of theme regions for the given theme.
   * Alternatively return the default theme if no theme is given.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array|\Drupal\cohesion\CohesionJsonResponse
   */
  public function getThemes(Request $request) {
    $themes_values = [];
    if (($themes = $this->themeHandler->listInfo())) {
      foreach ($themes as $theme_id => $theme) {
        $themes_values[] = [
          'value' => $theme_id,
          'name' => $theme->info['name'],
        ];
      }
    }

    $themes_values[] = [
      'value' => 'all',
      'name' => 'All themes',
    ];

    $error = empty($themes_values) ? TRUE : FALSE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $themes_values,
    ]);
  }

  /**
   * Return a list of themes.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array|\Drupal\cohesion\CohesionJsonResponse
   */
  public function getThemeRegions(Request $request) {
    // Check theme parameter.
    $themeParam = $request->attributes->get('theme');
    if ($themeParam == '__default__') {
      // Default theme.
      $theme = $this->config('system.theme')->get('default');
    }
    else {
      // Named theme, must exist.
      $themes = $this->themeHandler->listInfo();
      if (array_key_exists($themeParam, $themes)) {
        $theme = $themeParam;
      }
      else {
        // Theme is not a valid theme name.
        $invalid_error = TRUE;
      }
    }

    // Get a list of regions for the desired theme.
    $regions = [];
    if (isset($theme)) {
      if (($region_list = system_region_list($theme, BlockRepositoryInterface::REGIONS_ALL))) {
        foreach ($region_list as $key => $region) {
          $regions[] = [
            'value' => $key,
            'name' => $region,
          ];
        }
      }
    }
    // If "all themes" option is selected.
    if ($themeParam == 'all') {
      // Get all themes.
      $themes = $this->themeHandler->listInfo();
      foreach ($themes as $theme) {
        // Get themes regions.
        if (($region_list = system_region_list($theme, BlockRepositoryInterface::REGIONS_ALL))) {
          foreach ($region_list as $key => $region) {
            $region_key = array_column($regions, 'value');

            // Check if already in the array or not.
            if (!in_array($key, $region_key)) {
              $regions[] = [
                'value' => $key,
                'name' => $region,
              ];
            }
          }
        }
      }
    }

    $error = (empty($regions) || isset($invalid_error)) ? TRUE : FALSE;

    // Return built array.
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $regions,
    ]);
  }

  /**
   * Return a list of available contexts to Angular.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getContexts(Request $request) {

    $context = [];
    if ($this->moduleHandler->moduleExists('context')) {

      // Get all available contexts..
      $groups = \Drupal::service('context.manager')->getContextsByGroup();

      // Reformat into a grouped, keyed array for Angular.
      array_walk_recursive($groups, function (&$item, $key) use (&$context) {
        $context[] = [
          'label' => $item->getLabel(),
          'value' => 'context:' . $item->getName(),
        ];
      });
    }

    $error = !empty($context) ? FALSE : TRUE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $context,
    ]);
  }

  /**
   * Return a list of blocks.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getBlocks(Request $request) {
    $blocks_data = [];
    $theme = $request->attributes->get('theme');

    if (($blocks = $this->entityTypeManager
      ->getStorage('block')
      ->loadMultiple())) {
      foreach ($blocks as $block_id => $block) {
        if ($block->getTheme() == $theme) {
          $blocks_data[] = [
            'value' => $block_id,
            'label' => $this->t("@label (region: @region)", [
              '@label' => $block->label(),
              '@region' => $block->getRegion(),
            ]),
          ];
        }
        elseif ($theme == 'all') {
          $blocks_data[] = [
            'value' => $block_id,
            'label' => $this->t("@label (theme: @theme, region: @region)", [
              '@label' => $block->label(),
              '@theme' => $block->getTheme(),
              '@region' => $block->getRegion(),
            ]),
          ];
        }
      }
    }
    $error = !empty($blocks_data) ? FALSE : TRUE;

    array_multisort (array_column($blocks_data, 'label'), SORT_ASC, $blocks_data);

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $blocks_data,
    ]);
  }

  /**
   * Uses the 'entity.autocomplete_matcher' service to match entities.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function runAutoComplete(Request $request) {
    $data = [];
    if ($input = $request->query->get('q')) {
      $input_string = Tags::explode($input);
      $typed_string = mb_strtolower(array_pop($input_string));
      $target_type = $request->attributes->get('entity_type');
      $entity_storage = $this->entityTypeManager->getStorage($target_type);

      // Search via node UUID.
      if (Uuid::isValid($typed_string)) {
        $results = $entity_storage->loadByProperties(['uuid' => $typed_string]);
        $entity = reset($results);

        if ($entity) {
          $data[] = [
            'name' => $entity->label(),
            'id' => $entity->uuid(),
          ];
        }
      }

      // Search via everything else (if not already found via entity ID or UUID).
      if (strlen($typed_string) > 0 && !count($data)) {
        $bundles = $request->query->get('bundles');
        $selection_handler = $request->attributes->get('selection_handler');
        $selection_settings = [
          'match_operator' => 'CONTAINS',
          'target_bundles' => $bundles,
        ];

        $matches = $this->matcher->getMatches($target_type, $selection_handler, $selection_settings, $typed_string);

        foreach ($matches as $match) {
          // Extract the node ID.
          preg_match_all('#\((.*?)\)#', $match['value'], $var);
          $id = end($var[1]);
          $uuid = $entity_storage->load($id)->uuid();

          // Build the data array.
          $data[] = [
            'name' => $match['label'],
            'id' => $uuid,
          ];
        }
      }
    }

    $error = empty($data) ? TRUE : FALSE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   *
   */
  public function linkEntityTypes(Request $request) {
    $data = [];
    $entity_types_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types_definitions as $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface) {
        $data[] = [
          'label' => $entity_type->getLabel(),
          'value' => $entity_type->id(),
        ];
      }
    }

    $data[] = [
      'label' => $this->t('View'),
      'value' => 'view',
    ];

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $data,
    ]);
  }

  /**
   *
   */
  public function linkAutoComplete(Request $request) {
    $entity_types = $request->query->get('entity_types');

    $content_entity_types = [];
    $entity_types_definitions = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types_definitions as $entity_type) {
      if ($entity_type instanceof ContentEntityTypeInterface) {
        $content_entity_types[] = $entity_type;
      }
    }

    $grouped_data = [];
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

    if ($input = $request->query->get('q')) {
      $input_string = Tags::explode($input);
      $typed_string = mb_strtolower(array_pop($input_string));

      $query_split = explode('::', $typed_string);
      if (isset($query_split[0])) {
        if ($query_split[0] == 'view' && isset($query_split[1]) && isset($query_split[2])) {
          if ($view_type = $this->entityTypeManager->getStorage('view')) {
            $view = $view_type->load($query_split[1]);
            if ($view->access('view', \Drupal::currentUser())) {
              $executable = $view->getExecutable();
              $executable->initDisplay();
              foreach ($executable->displayHandlers as $display_id => $display) {

                // Search by view label and display title.
                if ($display_id == $query_split[2] && $display->hasPath()) {
                  if (!isset($grouped_data['views'])) {
                    $grouped_data['views'] = [];
                  }
                  $grouped_data['views'][] = [
                    'name' => "{$view->label()} - {$this->t($display->display['display_title'])}  (/{$display->getPath()})",
                    'id' => 'view::' . $view->id() . '::' . $display_id,
                    'group' => $this->t('Views'),
                  ];
                }
              }
            }
          }
        }
        elseif (isset($query_split[1]) && is_numeric($query_split[1])) {
          $entity_type = $this->entityTypeManager->getStorage($query_split[0]);
          if ($entity_type) {
            /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
            $entity = $entity_type->load($query_split[1]);
            if ($entity && $entity->access('view', \Drupal::currentUser()) && $entity->hasLinkTemplate('canonical')) {
              if (!isset($grouped_data[$entity->getEntityType()->id()])) {
                $grouped_data[$entity->getEntityType()->id()] = [];
              }
              if ($entity->hasTranslation($language)) {
                $entity = $entity->getTranslation($language);
              }
              $grouped_data[$entity->getEntityType()->id()][] = [
                'name' => $entity->label(),
                'id' => $query_split[0] . '::' . $entity->id(),
                'group' => $entity->getEntityType()->getLabel(),
              ];
            }
          }
        }
      }

      // Search via content entity ID.
      if (!count($grouped_data) && is_numeric($typed_string) && $typed_string > 0) {
        foreach ($content_entity_types as $content_entity_type) {
          $entity_type = $this->entityTypeManager->getStorage($content_entity_type->id());
          if ($entity_type) {
            /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
            $entity = $entity_type->load($typed_string);
            if ($entity && $entity->access('view', \Drupal::currentUser()) && $entity->hasLinkTemplate('canonical')) {
              if (!isset($grouped_data[$content_entity_type->id()])) {
                $grouped_data[$content_entity_type->id()] = [];
              }
              if ($entity->hasTranslation($language)) {
                $entity = $entity->getTranslation($language);
              }
              $grouped_data[$content_entity_type->id()][] = [
                'name' => $entity->label(),
                'id' => $content_entity_type->id() . '::' . $entity->id(),
                'group' => $content_entity_type->getLabel(),
              ];
            }
          }
        }
      }

      // Search via everything else (if not already found via node ID).
      if (strlen($typed_string) > 0 && !count($grouped_data)) {
        $selection_settings = [
          'match_operator' => 'CONTAINS',
        ];

        foreach ($content_entity_types as $content_entity_type) {
          /** @var \Drupal\Core\Entity\ContentEntityTypeInterface $content_entity_type */
          $entity_type = $this->entityTypeManager->getStorage($content_entity_type->id());

          try {
            if ($entity_type && (!$entity_types || is_array($entity_types) && in_array($content_entity_type->id(), $entity_types)) && $content_entity_type->hasLinkTemplate('canonical')) {
              $matches = $this->matcher->getMatches($content_entity_type->id(), 'default', $selection_settings, $typed_string);

              foreach ($matches as $match) {
                // Extract the node ID.
                preg_match('#\((.*?)\)#', $match['value'], $var);

                /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
                $entity = $entity_type->load($var[1]);
                if ($entity && $entity->access('view', \Drupal::currentUser()) && $entity->hasLinkTemplate('canonical')) {
                  if (!isset($grouped_data[$content_entity_type->id()])) {
                    $grouped_data[$content_entity_type->id()] = [];
                  }
                  $grouped_data[$content_entity_type->id()][] = [
                    'name' => $match['label'],
                    'id' => $content_entity_type->id() . '::' . $entity->id(),
                    'group' => $content_entity_type->getLabel(),
                  ];
                }
              }
            }
          }
          catch (\Exception $e) {
            // Some modules don't handle autocomplete so we ignore them.
          }
        }

        $view_type = $this->entityTypeManager->getStorage('view');
        if ($view_type && !$entity_types || is_array($entity_types) && in_array('view', $entity_types)) {
          $views = $view_type->loadMultiple();
          foreach ($views as $view) {
            if ($view->access('view', \Drupal::currentUser())) {
              $executable = $view->getExecutable();
              $executable->initDisplay();
              foreach ($executable->displayHandlers as $display_id => $display) {

                // Search by view label and display title.
                if ($display->hasPath() && (stripos($view->label(), $typed_string) !== FALSE || stripos($display->display['display_title'], $typed_string) !== FALSE)) {

                  if (!isset($grouped_data['views'])) {
                    $grouped_data['views'] = [];
                  }

                  $grouped_data['views'][] = [
                    'name' => "{$view->label()} - {$this->t($display->display['display_title'])}  (/{$display->getPath()})",
                    'id' => 'view::' . $view->id() . '::' . $display_id,
                    'group' => 'Views',
                  ];
                }
              }
            }
          }
        }

      }
    }

    $group_sort = ['node', 'views', 'taxonomy_term', 'user', 'media', 'file'];

    $data = [];
    foreach ($group_sort as $group) {
      if (isset($grouped_data[$group]) && !empty($grouped_data[$group])) {
        $data = array_merge($data, $grouped_data[$group]);
        unset($grouped_data[$group]);
      }
    }

    foreach ($grouped_data as $group_data) {
      $data = array_merge($data, $group_data);
    }

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $data,
    ]);
  }

  /**
   * Get all available image styles.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getMenus(Request $request) {

    // Get list of menus.
    $menus_data = [];
    if (($menus = \Drupal::entityTypeManager()
      ->getStorage('menu')
      ->loadMultiple())) {
      foreach ($menus as $key => $menu) {
        $menus_data[] = [
          'value' => $key,
          'name' => $menu->get('label'),
        ];
      }
    }
    $error = !empty($menus_data) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $menus_data,
    ]);
  }

  /**
   * Get all available image styles.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getImageStyles(Request $request) {

    $image_style_data = [];
    $image_style_data[] = [
      'label' => $this->t('No image style'),
      'value' => '',
    ];
    if (($image_styles = \Drupal::entityTypeManager()
      ->getStorage('image_style')
      ->loadMultiple())) {
      foreach ($image_styles as $image_style) {

        // Ignore image styles generated by the responsive image module.
        if (isset($image_style->get('dependencies')['enforced']['module'][0]) && $image_style->get('dependencies')['enforced']['module'][0] == 'responsive_image') {
          continue;
        }

        // Add all other image styles.
        $image_style_data[] = [
          'label' => $image_style->get('label'),
          'value' => $image_style->get('name'),
        ];
      }
    }

    $error = !empty($image_style_data) ? FALSE : TRUE;

    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $image_style_data,
    ]);
  }

  /**
   * Get all available entity types that support view modes.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function getEntityTypes(Request $request) {

    $restrict = $request->query->get('restrict', []);
    $storage = \Drupal::service('entity_type.manager')
      ->getStorage('entity_view_mode');
    $entity_ids = $storage->getQuery()->execute();
    $entities = $storage->loadMultiple($entity_ids);
    $entity_types_definition = \Drupal::service('entity_type.manager')
      ->getDefinitions();

    $entity_types = [];
    foreach ($entities as $entity) {
      if (empty($restrict) || in_array($entity->getTargetType(), $restrict)) {
        $entity_type = $entity_types_definition[$entity->getTargetType()];

        $view_builder = $entity_type->hasHandlerClass('view_builder');
        if ($entity_type instanceof ContentEntityTypeInterface && $view_builder) {
          $entity_types[$entity->getTargetType()] = [
            'value' => $entity->getTargetType(),
            'name' => $entity_type->getLabel(),
          ];
        }
      }

    }

    $entity_types = array_values($entity_types);

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $entity_types,
    ]);
  }

  /**
   *
   */
  public function getEntityBrowsers(Request $request) {

    $browsers = [];

    if ($this->moduleHandler()
      ->moduleExists('entity_browser') |
      $this->moduleHandler()
        ->moduleExists('media_library')) {

      if ($this->moduleHandler()
        ->moduleExists('media_library')) {

        $browsers[] = [
          'label' => 'Media library',
          'value' => 'media_library',
        ];
      }

      if ($this->moduleHandler()
        ->moduleExists('entity_browser')) {

        try {
          $entity_browsers = \Drupal::entityTypeManager()
            ->getStorage('entity_browser')
            ->loadMultiple();

          foreach ($entity_browsers as $entity_browser) {
            $browsers[] = [
              'label' => $entity_browser->label(),
              'value' => $entity_browser->id(),
            ];
          }

          $browsers[] = [
            'label' => 'Typeahead',
            'value' => 'typeahead',
          ];
        }
        catch (\Exception $e) {
        }
      }

      return new CohesionJsonResponse([
        'status' => 'success',
        'data' => $browsers,
      ]);
    }

    return new CohesionJsonResponse([
      'status' => 'error',
      'data' => ['error' => $this->t('This element requires the Entity browser or Media library module to be installed.')],
    ], 400);
  }

  /**
   *
   */
  public function getEntityTypesBundles(Request $request) {
    $types = [];
    try {
      $entity_type = $request->attributes->get('entity_type');
      $bundles = \Drupal::service('entity_type.bundle.info')->getBundleInfo($entity_type);

      foreach ($bundles as $bundle_id => $bundle) {
        $types[] = [
          'label' => $bundle['label'],
          'value' => $bundle_id,
        ];
      }
    }
    catch (\Exception $e) {
    }

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $types,
    ]);
  }

  /**
   *
   */
  public function getEntityBrowserUrl(Request $request) {

    $entity_browser_id = $request->query->get('entity_browser_id');
    $entity_type = $request->query->get('entity_type');
    $target_bundles_ids = $request->query->get('target_bundles');

    if ($this->moduleHandler()
      ->moduleExists('media_library') && $entity_browser_id == 'media_library') {

      $data = [];

      // If the media types are not set then allow all.
      if (!isset($target_bundles_ids)) {

        $target_bundles_ids = [];
        $media_types = \Drupal::service('entity_type.bundle.info')
          ->getBundleInfo('media');

        foreach ($media_types as $key => $media_type) {
          $target_bundles_ids[] = $key;
        }
      }

      $allowed_types = array_filter($target_bundles_ids);
      $allowed_media_types_query = http_build_query(['media_library_allowed_types' => $target_bundles_ids]);
      $selected_type = array_shift($target_bundles_ids);
      $media_lib_state = MediaLibraryState::create('media_library.opener.cohesion', $allowed_types, $selected_type, 1);

      $url = '/cohesion-media-library?coh_clean_page=true&media_library_opener_id=media_library.opener.cohesion&' . $allowed_media_types_query . '&media_library_selected_type=' . $media_lib_state->getSelectedTypeId() . '&media_library_remaining=1&hash=' . $media_lib_state->getHash() . '';

      $data = [
        'url' => $url,
      ];

      return new CohesionJsonResponse([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    if ($this->moduleHandler()
      ->moduleExists('entity_browser') && \Drupal::hasService('entity_browser.selection_storage')) {

      $data = [];
      if ($entity_browser_id && $entity_type) {

        try {

          if ($entity_browser = $this->entityTypeManager()
            ->getStorage('entity_browser')
            ->load($entity_browser_id)) {

            $display = $entity_browser->getDisplay();
            // Reset display id.
            $display->setUuid('');
            $url = $display->path() . '?uuid=' . $display->getUuid() . '&coh_clean_page=true';

            $target_bundles = [];
            if ($target_bundles_ids) {
              foreach ($target_bundles_ids as $target_bundle_id) {
                $target_bundles[$target_bundle_id] = $target_bundle_id;
              }
            }

            $persistent_data = [
              'validators' => [
                'entity_type' => [
                  'type' => $entity_type,
                ],
                'cardinality' => [
                  'cardinality' => 1,
                ],
              ],
              'selected_entities' => [],
              'widget_context' => [
                'target_bundles' => $target_bundles,
              ],
            ];

            \Drupal::service('entity_browser.selection_storage')
              ->setWithExpire($display->getUuid(), $persistent_data, Settings::get('entity_browser_expire', 21600));

            $data = [
              'url' => $url,
              'uuid' => $display->getUuid(),
              'cardinality' => 1,
              'selection_mode' => EntityBrowserElement::SELECTION_MODE_APPEND,
            ];
          }
        }
        catch (\Exception $e) {
        }
      }

      return new CohesionJsonResponse([
        'status' => 'success',
        'data' => $data,
      ]);
    }

    return new CohesionJsonResponse([
      'status' => 'error',
      'data' => ['error' => $this->t('This element requires the Entity browser or Media library module to be installed.')],
    ], 400);
  }

}
