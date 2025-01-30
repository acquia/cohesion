<?php

namespace Drupal\cohesion_templates\TwigExtension;

use Drupal\block\Entity\Block;
use Drupal\cohesion\Routing\CohesionCurrentRouteMatch;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Component\Uuid\Uuid;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Access\AccessResultAllowed;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Utility\Error;
use Drupal\Core\Utility\Token;
use Symfony\Component\Mime\MimeTypeGuesserInterface;
use Twig\Extension\AbstractExtension;
use Drupal\image\Entity\ImageStyle;
use Twig\Markup as TwigMarkup;
use Drupal\Core\Cache\CacheableMetadata;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Site studio twig extensions.
 *
 * @package Drupal\cohesion_templates\TwigExtension
 */
class TwigExtension extends AbstractExtension {

  const EXTERNAL_URI_SCHEMES = ['s3', 'acquia-dam', 'http', 'https'];

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */

  protected $renderer;

  /**
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * @var \Drupal\Core\Theme\Registry
   */
  protected $themeRegistry;

  /**
   * @var \Drupal\Core\Template\TwigEnvironment
   */
  protected $twigEnvironment;

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * @var \Symfony\Component\Mime\MimeTypeGuesserInterface
   */
  protected $extensionMimeTypeGuesser;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * The cohesion utils helper.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $loggerChannel;

  /**
   * Cohesion current route match services
   *
   * @var \Drupal\cohesion\Routing\CohesionCurrentRouteMatch
   */
  protected $cohesionCurrentRouteMatch;

  /**
   * The current user
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Custom Components Service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponentsService;

  /**
   * File Url Generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Entity Repository Service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * TwigExtension constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\Core\Utility\Token $token
   * @param \Drupal\Core\Theme\Registry $themeRegistry
   * @param \Drupal\Core\Template\TwigEnvironment $twigEnvironment
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   * @param \Symfony\Component\Mime\MimeTypeGuesserInterface $extension_mime_type_guesser
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   * @param \Drupal\cohesion\Routing\CohesionCurrentRouteMatch $cohesion_current_route_match
   * @param \Drupal\Core\Session\AccountInterface $current_user
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   */
  public function __construct(
    RendererInterface $renderer,
    Token $token,
    Registry $themeRegistry,
    TwigEnvironment $twigEnvironment,
    UuidInterface $uuid,
    EntityTypeManagerInterface $entity_type_manager,
    StreamWrapperManager $stream_wrapper_manager,
    MimeTypeGuesserInterface $extension_mime_type_guesser,
    ThemeManagerInterface $theme_manager,
    CohesionUtils $cohesion_utils,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    CohesionCurrentRouteMatch $cohesion_current_route_match,
    AccountInterface $current_user,
    FileUrlGeneratorInterface $fileUrlGenerator,
    EntityRepositoryInterface $entityRepository,
    UsageUpdateManager $usage_update_manager,
  ) {
    $this->renderer = $renderer;
    $this->token = $token;
    $this->themeRegistry = $themeRegistry;
    $this->twigEnvironment = $twigEnvironment;
    $this->uuid = $uuid;
    $this->entityTypeManager = $entity_type_manager;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->extensionMimeTypeGuesser = $extension_mime_type_guesser;
    $this->themeManager = $theme_manager;
    $this->cohesionUtils = $cohesion_utils;
    $this->loggerChannel = $loggerChannelFactory->get('cohesion_templates');
    $this->cohesionCurrentRouteMatch = $cohesion_current_route_match;
    $this->currentUser = $current_user;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->entityRepository = $entityRepository;
    $this->usageUpdateManager = $usage_update_manager;
    try {
      $this->customComponentsService = \Drupal::service('custom.components');
    } catch (\Exception $exception) {
    }
  }

  /**
   * Gets a unique identifier for this Twig extension.
   */
  public function getName() {
    return 'cohesion_templates';
  }

  /**
   * Generates a list of all Twig functions that this extension defines.
   */
  public function getFunctions() {
    return [
      new TwigFunction('processtoken', [$this, 'tokenReplace']),
      new TwigFunction('renderComponent', [$this, 'renderComponent']),
      new TwigFunction('renderInlineStyle', [$this, 'renderInlineStyle']),
      new TwigFunction('drupal_block', [$this, 'drupalBlock']),
      new TwigFunction('contextpasses', [$this, 'contextPasses']),
      new TwigFunction('drupal_view_item', [$this, 'drupalViewItem']),
      new TwigFunction('node_path', [$this, 'nodePath']),
      new TwigFunction('cohesion_image_style', [
        $this,
        'cohesionImageStyle',
      ]),
      new TwigFunction('cohesion_image_mime_type', [
        $this,
        'cohesionImageMimeType',
      ]),
      new TwigFunction('cohesion_views_exposed', [
        $this,
        'cohesionViewsExposed',
      ]),
      new TwigFunction('cohesion_views_filter_label', [
        $this,
        'cohesionViewsFilterLabel',
      ]),
      new TwigFunction('cohesion_views_pagination', [
        $this,
        'cohesionViewsPagination',
      ]),
      new TwigFunction('render_menu', [$this, 'renderMenu']),
      new TwigFunction('customElement', [$this, 'customElement']),
      new TwigFunction('printr', [$this, 'printr']),
      new TwigFunction('cohesion_breadcrumb', [$this, 'getBreadCrumb']),
      new TwigFunction('get_menu_item_attributes', [
        $this,
        'getMenuItemAttributes',
      ]),
      new TwigFunction('range_test', [$this, 'rangeTest']),
      new TwigFunction('castToString', [$this, 'castToString']),
      new TwigFunction('path_renderer', [$this, 'pathRenderer']),
      new TwigFunction('format_wysiwyg', [$this, 'formatWysiwyg']),
      new TwigFunction('renderContent', [$this, 'renderContent']),
      new TwigFunction('get_content_language', [
        $this,
        'getContentLanguage',
      ]),
      new TwigFunction('patchYoutubeUrl', [$this, 'patchYoutubeUrl']),
      new TwigFunction('youtubeVideoId', [$this, 'youtubeVideoId']),
      new TwigFunction('has_drupal_permission', [
        $this,
        'hasDrupalPermission',
      ]),
      new TwigFunction('escape_url', [$this, 'escapeURL']),
      new TwigFunction('isActiveTheme', [$this, 'isActiveTheme']),
      new TwigFunction('getComponentFieldValue',
        [
          $this,
          'getComponentFieldValue',
        ],
        [
          'needs_context' => TRUE,
        ]
      ),
      new TwigFunction('setViewIterate', [$this, 'setViewIterate'], ['needs_context' => TRUE]),
      new TwigFunction('getViewIterate', [$this, 'getViewIterate'], ['needs_context' => TRUE]),
      new TwigFunction('incrementViewIterate',
        [
          $this,
          'incrementViewIterate',
        ],
        [
          'needs_context' => TRUE,
        ]
      ),
      new TwigFunction('frontendBuilderDropzone',
        [
          $this,
          'frontendBuilderDropzone',
        ],
        [
          'needs_context' => TRUE,
        ]
      ),
      new TwigFunction('isFrontendEditor', [$this, 'isFrontendEditor'], []),
      new TwigFunction('coh_instanceid', [$this, 'cohInstanceId'], [
        'needs_context' => TRUE,
      ]),
      new TwigFunction('render_field_uuid', [$this, 'renderFieldUuid'], [
        'needs_context' => TRUE,
      ]),
      new TwigFunction('renderStyles', [$this, 'renderStyles']),
    ];
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new TwigFilter('int', [$this, 'parseInt']),
      new TwigFilter('crc32', [$this, 'cohCrc32']),
      new TwigFilter('clean', [$this, 'clean']),
      new TwigFilter('coh_raw', [$this, 'cohRaw']),
    ];
  }

  /**
   * Used for tokenized inline styles.
   */
  public function cohInstanceId($_context) {
    $instance_name = 'ssa-instance-';
    $instance_id = '';
    if (isset($_context['parentContext']['elements'])) {
      foreach ($_context['parentContext']['elements'] as $element) {
        if ($element instanceof EntityInterface) {
          $instance_id .= $element->uuid();
          break;
        }
      }
    }

    if (isset($_context['parentContext']['componentUuid'])) {
      $instance_id .= $_context['parentContext']['componentUuid'];
    }

    if (isset($_context['componentUuid'])) {
      $instance_id .= $_context['componentUuid'];
    }

    if (isset($_context['componentUuid'], $_context['componentFieldsValues'])) {
      $instance_id = md5(serialize($_context['componentFieldsValues']));
    }

    if ($instance_id == '') {
      $instance_id = $this->uuid->generate();
    }

    return $instance_name . $instance_id;
  }

  /**
   * Is the supplied theme name the same as the active theme?
   *
   * @param $theme
   *
   * @return bool
   */
  public function isActiveTheme($theme) {
    return \Drupal::theme()->getActiveTheme()->getName() == $theme;
  }

  /**
   * Fixes a similar double escaping issue:
   * https://www.drupal.org/project/drupal/issues/2598502.
   *
   * @param $string
   *
   * @return string
   */
  public function castToString($string) {
    return (string) $string;
  }

  /**
   * Fixes youtube urls so they are consistent.
   *
   * @param $string
   *   - Youtube url in any format shown here
   *   https://regex101.com/r/x3JCIu/1
   *
   * @return string - youtube url in format
   *   https://www.youtube.com/embed/VIDEO_ID
   */
  public function patchYoutubeUrl($string) {
    if ($videoId = $this->youtubeVideoId($string)) {
      return "https://www.youtube.com/embed/" . $videoId;
    }
    return 'https://www.youtube.com/embed/';
  }

  /**
   * @param $string
   * @return mixed|string
   */
  public function youtubeVideoId($string) {
    if (preg_match('/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/', $string, $matches)) {
      return $matches[1];
    }
    return '';
  }

  /**
   * Converts "name://" style stream wrappers to relative URLs.
   * (Used for component fields in styles).
   *
   * @param $content
   *
   * @return mixed
   */
  private function streamWrappers($content) {
    $local_stream_wrappers = $this->streamWrapperManager->getWrappers(StreamWrapperInterface::NORMAL);

    if ($local_stream_wrappers) {
      foreach ($local_stream_wrappers as $scheme => $value) {

        $stream_wrapper = $this->streamWrapperManager->getViaScheme($scheme);
        $base_path = \Drupal::request()->getSchemeAndHttpHost();

        try {
          $uri = $scheme . '://';
          $stream_wrapper->setUri($uri . 'test.txt');
          $external_url = $stream_wrapper->getExternalUrl();

          $stream_path = str_replace($base_path, '', $external_url);
          $stream_path = str_replace('test.txt', '', $stream_path);

          $content = str_replace($uri, $stream_path, $content);
        }
        catch (\Exception $e) {
          $this->loggerChannel->error($e->getTraceAsString());
        }
      }
    }

    return $content;
  }

  /**
   * Render styles inline (for components with fields and/or tokens).
   *
   * @param $content
   *
   * @return \Drupal\Component\Render\MarkupInterface|mixed
   *
   * @throws \Exception
   */
  public function renderInlineStyle($content) {
    if (!in_array($this->themeManager->getActiveTheme()->getName(), $this->cohesionUtils->getCohesionTemplateOnlyEnabledThemes())) {
      $content = $this->streamWrappers($content);
      // Allow quotes in inline styles.
      $content = str_replace('&quot;', '"', $content);
      $content = str_replace('&#039;', "'", $content);
      $content = str_replace('&amp;', '&', $content);

      $is_ajax = \Drupal::request()->isXmlHttpRequest();

      // If it's an ajax call or a preview render style inline other send to
      // the head
      if ($is_ajax) {
        return $content;
      }
      else {
        $element = ['#attached' => ['cohesion' => [$content]]];
        $this->renderer->render($element);
      }
    }
    return [];
  }

  /**
   * Tests a print style range. (e.g. "1-4, 6, 9, 12,15")
   *
   * @param $index
   * @param $range_string
   *
   * @return bool
   */
  public function rangeTest($index, $range_string) {

    $haystack = preg_replace_callback('/(\d+)-(\d+)/', function ($m) {
      return implode(',', range($m[1], $m[2]));
    }, $range_string);

    $haystack = explode(',', $haystack);

    return in_array($index, $haystack);
  }

  /**
   * Debug function.
   *
   * @param $input
   *
   * @return string
   */
  public function printr($input) {
    ob_start();
    print_r($input);
    $return = ob_get_contents();
    ob_end_clean();
    return $return;
  }

  /**
   * Removes start and end padding and newlines form string (used mostly for
   * rendering JSON string inside templates).
   *
   * @param $input
   *
   * @return string
   */
  public function clean($input) {

    // Remove leading and trailing whitspace.
    $input = implode("\n", array_map("trim", explode("\n", $input)));

    // Remove newlines.
    $input = str_replace("\n", " ", $input);

    // Compress multiple spaces to a single space.
    $input = preg_replace('/\s+/', ' ', $input);

    // Return it!
    return trim($input);
  }

  /**
   * Replaces a given tokens with appropriate value.
   *
   * @param string $token
   *   A replaceable token.
   * @param array $data
   *   (optional) An array of keyed objects. For simple replacement scenarios
   *   'node', 'user', and others are common keys, with an accompanying node or
   *   user object being the value. Some token types, like 'site', do not
   *   require any explicit information from $data and can be replaced even if
   *   it is empty.
   * @param array $_context
   *   (optional) The twig context.
   *
   * @return string
   *   The token value.
   *
   * @see \Drupal\Core\Utility\Token::replace()
   */
  public function tokenReplace($token, $data = NULL, $_context = [], $isTemplate = FALSE) {
    // This is a menu link content token, so we need to drill into the "item"
    // variable to find the content.
    if (array_key_exists('menu_link_content', $data) || array_key_exists('menu-link', $data)) {
      // menu_item_extras module installed.
      if (isset($_context['item']['content']['#menu_link_content'])) {
        $data[key($data)] = $_context['item']['content']['#menu_link_content'];
      }
      // Default drupal.
      elseif (isset($_context['item']['original_link'])) {
        $def = $_context['item']['original_link']->getPluginDefinition();
        $def = explode(':', $def['id']);

        // Load the menu link content as an entity.
        try {
          if (isset($def[0]) && isset($def[1])) {
            if ($entity = $this->entityRepository->loadEntityByUuid($def[0], $def[1])) {

              // Use this as the context for the token replacement.
              $data[key($data)] = $entity;
            }
          }
        }
        catch (\Throwable $e) {
          $this->loggerChannel->error($e->getTraceAsString());
        }
      }
    }
    // If this is a generic content token (from tokens_tokens), patch the
    // correct data into $data when a content entity module doesn't implement
    // hook_tokens() - for example, block_content.
    else {
      if (empty($data[key($data)])) {
        if (isset($_context['content']['#' . key($data)])) {
          // Allow tokens_token to render this (the.
          $data[key($data)] = $_context['content']['#' . key($data)];
        }
        // If menu_name available, load the menu entity.
        if (isset($_context['menu_name'])) {
          $menu = $this->entityTypeManager->getStorage('menu')->load($_context['menu_name']);
          $data[key($data)] = $menu;
        }
      }
    }

    // https://www.drupal.org/node/2580723#comment-11249413
    $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
    foreach ($data as $item) {
      if ($item instanceof ContentEntityInterface) {
        $language = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
      }
    }
    $token_replacement = $this->token->replace(new HtmlEscapedText($token), $data, [
      'langcode' => $language,
      'clear' => TRUE,
    ]);

    // If layout canvas, decode entities as component will take care of
    // escaping.
    if (!$isTemplate) {
      $token_replacement = Html::decodeEntities($token_replacement);
    }

    return Markup::create($token_replacement);
  }

  /**
   * Provides component markup to the visual page builder.
   *
   * @param $renderer
   * @param $_context
   * @param $componentInstanceUuid
   * @param $component_content_uuid
   * @param $component_content_id
   * @return array
   */
  public function addComponentFrontEndBuilderMarkup($renderer, $_context, $componentInstanceUuid, $component_content_uuid = NULL, $component_content_id = NULL) {

    $build = [];

    if ($this->isFrontendEditor() && $this->isParentContent($_context) && !isset($_context['hideContextualLinks']) && !isset($_context['isPreview']) && !isset($_context['view']) && $this->hasDrupalPermission("access components")) {

      $coh_start = [
        '#type' => 'container',
        '#attributes' => [
          'data-ssa-start' => [$componentInstanceUuid],
        ],
      ];

      if ($component_content_uuid !== NULL) {
        $coh_start['#attributes']['data-ssa-component-content-uuid'] = $component_content_uuid;
      }

      if ($component_content_id !== NULL) {
        $coh_start['#attributes']['data-ssa-component-content-id'] = $component_content_id;
      }

      $build[] = $coh_start;

      $build[] = $renderer;

      $build[] = [
        '#type' => 'container',
        '#attributes' => [
          'data-ssa-end' => [$componentInstanceUuid],
        ],
      ];
    } else {
      $build[] = $renderer;
    }

    return $build;

  }

  /**
   * Render a component (handles caching, styles, etc).
   *
   * @param $componentId
   * @param $isTemplate
   * @param $_context
   * @param $componentFields
   * @param null $componentInstanceUuid
   * @param false $componentContentId
   *
   * @return array | \Drupal\Component\Render\MarkupInterface
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \LogicException
   * @throws \Exception
   */
  public function renderComponent($componentId, $isTemplate, $_context, $componentFields, $componentInstanceUuid = NULL, $componentContentId = FALSE) {

    // Render component content if specified.
    if ($componentContentId && (!isset($_context['component_content']) || $_context['component_content'] instanceof ComponentContent && $_context['component_content']->uuid() !== $componentContentId)) {
      /** @var \Drupal\cohesion_elements\Entity\ComponentContent $componentContent */

      // If the component content is referenced via a UUID.
      if (Uuid::isValid($componentContentId)) {
        $componentContents = $this->entityTypeManager
          ->getStorage('component_content')
          ->loadByProperties(['uuid' => $componentContentId]);
        $componentContent = reset($componentContents);
      }
      else {
        // If not then fallback to load via ID.
        $componentContent = $this->entityTypeManager->getStorage('component_content')->load($componentContentId);
      }

      if ($componentContent && $componentContent->isPublished() && $componentContent->access('view', $this->currentUser)) {
        $view_builder = $this->entityTypeManager->getViewBuilder('component_content');
        $build = $view_builder->view($componentContent);
        $renderer = $build;
        return $this->addComponentFrontEndBuilderMarkup($renderer, $_context, $componentInstanceUuid, $componentContent->uuid(), $componentContent->id());
      }
      return [];
    }

    // First, check the component actually exists.
    // @todo this can probably be moved further down and in buildComponentFields and would benefit from a loadMultiple
    /** @var \Drupal\cohesion_elements\Entity\Component $component */
    $component = $this->entityTypeManager->getStorage('cohesion_component')
      ->load($componentId);
    if ($component) {
      $component_model = $component->getLayoutCanvasInstance()->iterateModels();
      // Patch in the value for the component fields.
      $componentFieldsValues = [];
      foreach ($componentFields as $uuid => $component_field_id) {
        $componentFieldsValues[$uuid] = $this->buildComponentFields($component_field_id, $uuid, $component_model, $_context, $isTemplate);
      }

      // Build the render array.
      $context_cache_metadata = \Drupal::service('cohesion_templates.context.cache_metadata');
      $context_names = $context_cache_metadata->extractContextNames($component, $componentFieldsValues);
      if (!empty($context_names)) {
        $cache = $context_cache_metadata->getContextsCacheMetadata($context_names);
      }
      else {
        $cache = [];
      }

      $cache['tags'][] = 'component.cohesion.' . $componentId;
      $cache['tags'] = array_merge($cache['tags'], $component->getCacheTags());
      if (isset($cache['contexts'])) {
        $cache['contexts'] = array_merge($cache['contexts'], $component->getCacheContexts());
      }
      else {
        $cache['contexts'] = $component->getCacheContexts();
      }

      $template = 'component__cohesion_' . str_replace('-', '_', $componentId);

      $renderer = [
        '#theme' => $template,
        '#cache' => $cache,
        '#parentContext' => $_context,
        // This is used inside preprocess_cohesion_elements_component()
        '#parentIsComponent' => TRUE,
        // Tell children elements that its parent is a component
        // (for components in component)
        '#componentFieldsValues' => $componentFieldsValues,
        '#componentUuid' => $componentInstanceUuid,
        '#template' => $template,
      ];

      return $this->addComponentFrontEndBuilderMarkup($renderer, $_context, $componentInstanceUuid);
    }

    // If the element is not a loadable component it might be a custom component
    if (isset($this->customComponentsService)) {
      try {
        $custom_components = $this->customComponentsService->getComponents();
        if (isset($custom_components[$componentId])) {
          $component_layout_instance = $custom_components[$componentId]['form'];
          $component_model = $component_layout_instance->iterateModels();
          // Extract and build all the values to be used for output
          $componentFieldsValues = [];
          $dropzones = [];
          foreach ($componentFields as $uuid => $component_field_id) {
            if (isset($component_model[$uuid])) {
              $value = $this->buildComponentFields($component_field_id, $uuid, $component_model, $_context, $isTemplate);
              if ($component_model[$uuid]->getElement()->getProperty('uid') == 'component-drop-zone-placeholder') {
                $machine_name = 'dropzone-' . $component_model[$uuid]->getElement()->getProperty('uuid');
              } else {
                $machine_name = $component_model[$uuid]->getProperty([
                  'settings',
                  'machineName',
                ]);
              }

              // For consistency and for because twig requires it, all machine
              // name are underscores only.
              $machine_name = str_replace('-', '_', $machine_name);
              if ($component_model[$uuid]->getElement()->getProperty('uid') == 'component-drop-zone-placeholder') {
                $dropzones[$machine_name] = $value;
              } else {
                $componentFieldsValues[$machine_name] = $value;

              }
            }
          }

          // Custom component render array
          $renderer = [
            '#theme' => 'custom_component',
            '#field' => [],
            '#dropzones' => $dropzones,
            '#attributes' => [],
            '#attached' => [
              'library' => [
                'cohesion/custom-component-' . str_replace('_', '-', $componentId),
              ],
            ],
          ];

          // Custom component can either be renderer with twig (the definition
          // Yaml will contain a template entry), or use the default template
          // (see cohesion_elements/templates/custom-component.html.twig) in
          // which can be output a html template
          if (!is_null($custom_components[$componentId]['template'])) {
            // Attach the fields to the render array so that users can display
            // them.
            $renderer['#field'] = $componentFieldsValues;

            $renderer['#theme'] = 'custom_component_' . str_replace('-', '_', $custom_components[$componentId]['template']);
            $renderer['#template'] = $custom_components[$componentId]['template'];
          } else {

            // If the custom component is using the default twig template
            // we add all fields as data attributes on the div of the template
            foreach ($componentFieldsValues as $machine_name => &$field) {
              if ($field instanceof TwigMarkup) {
                $field = $field->__toString();
              }
            }
            $renderer['#attributes'] = [
              'class' => [$componentId],
              'id' => 'custom_component_' . $componentInstanceUuid,
            ];
            $renderer['#attributes']['data-ssa-custom-component'] = json_encode($componentFieldsValues);

            // The custom component definition contains a HTML template to be
            // rendered inside the custom-component default twig
            if (!is_null($custom_components[$componentId]['html'])) {
              $renderer['#html_template'] = !is_null($custom_components[$componentId]['html']) ? file_get_contents($custom_components[$componentId]['html']) : '';
            }
          }

          return $this->addComponentFrontEndBuilderMarkup($renderer, $_context, $componentInstanceUuid);
        }
      }
      catch (\Exception $exception) {
        $this->loggerChannel->error($exception->getMessage(), Error::decodeException($exception));
      }
    }
  }

  /**
   * Add and start element or end element (div) to the DOM to delimiter a
   * dropzone area for the frontend builder
   *
   * @param $_context
   * @param $element_uuid
   * @param $startStop
   *
   * @return array|void
   */
  public function frontendBuilderDropzone($_context, $element_uuid, $startStop) {
    // This only applies on the frontend builder endpoint
    // and only for components, not component content.
    if ($this->isFrontendEditor() && $this->isParentContent($_context) && !isset($_context['component_content']) && !isset($_context['hideContextualLinks']) && !isset($_context['isPreview']) && $this->hasDrupalPermission("access components")) {
      return [
        '#type' => 'container',
        '#attributes' => [
          'data-ssa-dropzone-' . $startStop => [$element_uuid],
        ],
      ];
    }
  }

  /**
   * Return TRUE if the current route is the frontend builder route, FALSE
   * otherwise
   *
   * @return bool
   */
  public function isFrontendEditor() {
    if ($this->cohesionCurrentRouteMatch->getRouteObject()) {
      return $this->cohesionCurrentRouteMatch->getRouteObject()->getOption('sitestudio_build') == 'TRUE';
    }
    return FALSE;
  }

  /**
   * Return TRUE if is the parent content, otherwise FALSE.
   * This is used for the page builder to show markup on the outter
   * component & its drop zones.
   *
   * @param $_context
   * @return bool
   */
  public function isParentContent($_context) {
    $current_route_entities = $this->cohesionCurrentRouteMatch->getRouteEntities();
    // Whether the component being rendered is part of the main page or a nested
    // content entity (ex: block content)
    foreach ($current_route_entities as $current_route_entity) {
      foreach ($_context as $context_item) {
        if ($context_item instanceof EntityInterface &&
          $current_route_entity->getEntityTypeId() === $context_item->getEntityTypeId() &&
          $current_route_entity->id() === $context_item->id() &&
          (!isset($_context['parentContext']) || !in_array($context_item, $_context['parentContext']))) {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * @param $component_field_id
   * @param $uuid
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel[] $component_model
   * @param $_context
   * @param $isTemplate
   *
   * @return array|string|null
   */
  private function buildComponentFields($component_field_id, $uuid, $component_model, $_context, $isTemplate) {
    if (is_object($component_field_id)) {
      // Dropzone contents.
      return $component_field_id;
    }
    elseif (is_array($component_field_id)) {
      // Object token field.
      $componentFields = [];
      foreach ($component_field_id as $key => $v) {
        // If the key on the values exists in the model then it's a multifield
        // value.
        if (isset($component_model[$key])) {
          $componentFields['#' . $key] = $this->buildComponentFields($v, $key, $component_model, $_context, $isTemplate);
        }
        else {
          // Enclose key with # so not treated as child in render array.
          $componentFields['#' . $key] = $this->buildComponentFields($v, $uuid, $component_model, $_context, $isTemplate);
        }
      }
      return $componentFields;
    }
    else {
      // Standard token field.
      return $this->processComponentFieldValue($component_field_id, $uuid, $component_model, $_context, $isTemplate);
    }
  }

  /**
   * This looks like is patches component field values at runtime.
   *
   * @param $component_field_id
   * @param $uuid
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel[] $component_model
   * @param $_context
   * @param $isTemplate
   *
   * @return string|null
   */
  private function processComponentFieldValue($component_field_id, $uuid, $component_model, $_context, $isTemplate) {
    if (isset($_context[$component_field_id]) && isset($component_model[$uuid]) && $component_model[$uuid]->getProperty(['settings'])) {
      $fieldValue = $_context[$component_field_id];
      // Escape values for security reason
      // Escape only once at the top most parent component, child component
      // (component in component) should not escape again, also the model in a
      // component is considered secure as it is done by site builder and they
      // should have as much power as possible.
      $toggle_type = $component_model[$uuid]->getProperty(
        ['settings', 'toggleType']
      );
      if (!$isTemplate || !empty($toggle_type)) {
        $default_value = $component_model[$uuid]->getProperty(['model', 'value']);
        $fieldValue = $this->cohesionUtils->processFieldValues($fieldValue, $component_model[$uuid], $default_value);
      }
      return $fieldValue;
    }
    return NULL;
  }

  /**
   * Builds the render array for the provided block.
   *
   * @param $id
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function drupalBlock($id) {
    $build = [];

    if (is_object($id) && $id instanceof TwigMarkup) {
      $id = $id->__toString();
    }

    $block = $this->entityTypeManager->getStorage('block')->load($id);
    if ($block instanceof Block) {
      $access = $block->access('view', $this->currentUser, TRUE);
      $cache_metadata = CacheableMetadata::createFromObject($access);
      if ($access instanceof AccessResultAllowed) {
        $build = $this->entityTypeManager->getViewBuilder('block')->view($block);
      }
      else {
        $build = [
          "#cache" => [
            "contexts" => $block->getCacheContexts(),
            "tags" => $block->getCacheTags(),
            "max-age" => $block->getCacheMaxAge(),
          ],
        ];
      }
      $build['#cache']['contexts'] = array_merge($build['#cache']['contexts'], $cache_metadata->getCacheContexts());
      $build['#cache']['tags'] = array_merge($build['#cache']['tags'], $cache_metadata->getCacheTags());
    }

    return $build;
  }

  /**
   * Tests a context.
   *
   * @param $context_values
   * @param string $condition
   *
   * @return bool
   */
  public function contextPasses($context_values, $condition = 'AND') {
    if (!in_array($condition, ["AND", "OR"])) {
      return TRUE;
    }

    // Sanity check.
    if (!count($context_values) || !isset($context_values[0]) || !$context_values[0] instanceof TwigMarkup) {
      return TRUE;
    }

    $contexts = [];

    $context_values = $context_values[0]->__toString();
    $context_values = explode(',', $context_values);

    foreach ($context_values as $context_value) {
      $context_data = explode(':', trim($context_value));
      if ((!isset($context_data[1]) || $context_data[0] == 'context') && $context_data[0] != '') {
        $contexts[] = $context_data[1] ?? $context_data[0];
      }
    }

    $moduleHandler = \Drupal::service('module_handler');
    if ($moduleHandler->moduleExists('context')) {

      $context_manager = \Drupal::service('context.manager');

      $active_contexts = [];

      foreach ($context_manager->getActiveContexts() as $context) {
        $active_contexts[] = $context->getName();
      }

      // Loop through the contexts.
      foreach ($contexts as $context_name) {
        // Could not find set context in the active contexts, so fail (AND).
        if ($condition == 'AND' && !in_array($context_name, $active_contexts)) {
          return FALSE;
        }
        elseif ($condition == 'OR' && in_array($context_name, $active_contexts)) {
          return TRUE;
        }
      }
    }

    // Get active contexts on this page.
    return $condition == 'AND';
  }

  /**
   * Alias of Jinja (int) filter.
   *
   * @param $input
   *
   * @return mixed
   */
  public function parseInt($input) {
    return intval($input);
  }

  /**
   * Render the view mode of the first element in the $rows array.
   *
   * @param $view_modes
   * @param $rows
   *
   * @return array|string
   *
   * @throws \LogicException
   *   When called outside of a render context (i.e. outside of a renderRoot(),
   *   renderPlain() or executeInRenderContext() call).
   * @throws \Exception
   *   If a #pre_render callback throws an exception, it is caught to mark the
   *   renderer as no longer being in a root render call, if any. Then the
   *   exception is rethrown.
   */
  public function drupalViewItem($view_modes, $rows) {
    if (isset($rows['#rows'])) {

      // Get the first element from the array.
      $resultrow = array_shift($rows['#rows']);

      if (isset($resultrow['#view_mode'])) {

        $entity_type = NULL;
        $entity = NULL;
        foreach ($resultrow as $result_value) {
          if ($result_value instanceof ContentEntityInterface) {
            $entity = $result_value;
            $entity_type = $result_value->getEntityTypeId();
            break;
          }
        }

        if (!is_array($view_modes)) {
          $view_modes = Json::decode($view_modes);
        }

        if (is_array($view_modes)) {
          $view_modes_bundle = [];
          // Add view modes for the current entity type per bundle to an key
          // value array.
          foreach ($view_modes as $view_mode_data) {
            // Cast any TwigMarkup values to strings.
            foreach ($view_mode_data as $view_mode_data_key => $view_mode_data_value) {
              if ($view_mode_data_value instanceof TwigMarkup) {
                $view_mode_data[$view_mode_data_key] = $view_mode_data_value->__toString();
              }
            }

            // We only register defined view modes for the entity type of the
            // current entity. We don't register view mode set as default as it
            // is already in the resultrow array.
            if ($view_mode_data['entity_type'] == $entity_type && $view_mode_data['view_mode'] !== 'default') {

              // Bundle may not be in the json in older version.
              if (!isset($view_mode_data['bundle'])) {
                $view_modes_bundle['all'] = $view_mode_data['view_mode'];
              }
              else {
                $view_modes_bundle[$view_mode_data['bundle']] = $view_mode_data['view_mode'];
              }

            }
          }

          // Change the view mode served by the view to the view mode specified
          // in Cohesion. If none exists (for the bundle or if no `all` has
          // been specified) it will fall back the one in the Drupal view.
          if (isset($view_modes_bundle[$entity->bundle()])) {
            $resultrow['#view_mode'] = $view_modes_bundle[$entity->bundle()];
          }
          elseif (isset($view_modes_bundle['all'])) {
            $resultrow['#view_mode'] = $view_modes_bundle['all'];
          }
        }
        else {
          // For backward compatibility.
          $resultrow['#view_mode'] = $view_modes;
        }

        if (!in_array($resultrow['#view_mode'], $resultrow['#cache']['keys'])) {
          $resultrow['#cache']['keys'][] = $resultrow['#view_mode'];
        }
      }

      return $this->renderer->render($resultrow);
    }
  }

  /**
   * Return a path alias given a node ID.
   *
   * @param null $nid
   *
   * @return string
   */
  public function nodePath($nid = NULL) {
    if (!$nid) {
      return '';
    }

    // Return value was not numeric, so just return.
    if (!is_numeric($nid)) {
      return $nid;
    }

    // Numeric ID sent, so get the path.
    try {
      $alias = \Drupal::service('path_alias.manager')
        ->getAliasByPath('/node/' . $nid);
      return $alias;
    }
    catch (\Exception $e) {
      $this->loggerChannel->error($e->getTraceAsString());
      return '';
    }
  }

  /**
   * Return the FQURL for an image with a given style.
   *
   * @param $file_uri
   * @param $image_style
   *
   * @return \Drupal\Core\GeneratedUrl|string
   */
  public function cohesionImageStyle($file_uri, $image_style) {
    // If created as a component field, the image style will come as
    // TwigMarkup.
    if (is_object($image_style) && $image_style instanceof TwigMarkup) {
      $image_style = $image_style->__toString();
    }

    $file_uri = html_entity_decode(stripcslashes($file_uri));
    if (!\Drupal::service('stream_wrapper_manager')->isValidUri($file_uri)) {
      return $file_uri;
    }

    // Use external URLs for images stored in Acquia DAM or Amazon S3.
    if (in_array(StreamWrapperManager::getScheme($file_uri), self::EXTERNAL_URI_SCHEMES)) {
      $image_style_instance = $this->entityTypeManager->getStorage('image_style')->load($image_style);
      $url = $this->fileUrlGenerator->generateAbsoluteString($file_uri);

      // Check if the image style instance exists and supports the absolute URL.
      if ($image_style_instance instanceof ImageStyle && $image_style_instance->supportsUri($url)) {
        return $image_style_instance->buildUrl($file_uri);
      }

      return $url;
    }

    // Try and load the given image style.
    try {
      if ($image_style != '') {
        $image_style_instance = $this->entityTypeManager->getStorage('image_style')->load($image_style);
        if ($image_style_instance instanceof ImageStyle && $image_style_instance->supportsUri($file_uri)) {
          $url = $image_style_instance->buildUrl($file_uri);
          $url = parse_url($url);
          $decoded = $url['path'];

          if (isset($url['query']) && !empty($url['query'])) {
            $decoded .= '?' . $url['query'];
          }

          return $decoded;
        }
      }
    }
    catch (\Error $e) {
      $this->loggerChannel->error($e->getTraceAsString());

    }

    // No image style, unsupported file, or error, just render the full image.
    $url = $this->fileUrlGenerator->generateAbsoluteString($file_uri);
    $url = parse_url($url);
    $decoded = $url['path'];
    if (isset($url['query']) && !empty($url['query'])) {
      $decoded .= '?' . $url['query'];
    }

    return $decoded;
  }

  /**
   *
   */
  public function cohesionImageMimeType($file_uri, $image_style) {
    // If created as a component field, the image style will come as
    // TwigMarkup.
    if (is_object($image_style) && $image_style instanceof TwigMarkup) {
      $image_style = $image_style->__toString();
    }

    $file_uri = html_entity_decode(stripcslashes($file_uri));

    $extension = pathinfo($file_uri, PATHINFO_EXTENSION);
    $fake_path = 'dx8_image.' . $extension;
    if ($image_style != '') {
      if ($image_style_entity = $this->entityTypeManager->getStorage('image_style')->load($image_style)) {
        $fake_path = 'dx8_image.' . $image_style_entity->getDerivativeExtension($extension);
      }
    }

    return $this->extensionMimeTypeGuesser->guessMimeType($fake_path);
  }

  /**
   * @param $view
   * @param $filter
   * @param $display_type
   * @param $reloadOnChange
   * @param $showLabel
   *
   * @return array
   */
  public function cohesionViewsExposed($view, $filter, $display_type, $reloadOnChange, $showLabel) {

    // This might not get set later on.
    $filter_identifier = NULL;

    // Get the complete exposed form.
    $exposed_form = $view->exposed_widgets;
    $submit_button_id = '';
    if (isset($exposed_form['actions']['submit'])) {
      $submit_button_id = '#' . $exposed_form['actions']['submit']['#id'];
    }
    $reset_button_id = '';
    if (isset($exposed_form['actions']['reset'])) {
      $reset_button_id = '#' . $exposed_form['actions']['reset']['#id'];
    }
    $markup = [];

    // Sort filter may have the same id as filter id so they are prefixed bu
    // coh_sort_.
    $is_sort = FALSE;
    if (strpos($filter, 'coh_sort_') === 0) {
      $filter = substr($filter, strlen('coh_sort_'));
      $is_sort = TRUE;
    }

    // If better exposed filter combined.
    $is_bef_combined = FALSE;
    if (isset($exposed_form['sort_bef_combine'])) {
      $is_bef_combined = TRUE;
    }

    if ($is_bef_combined) {
      $filter_identifier = $filter;

      $markup[$filter_identifier] = [
        '#type' => 'select',
        '#name' => 'sort_bef_combine',
        '#options' => $exposed_form['sort_bef_combine']['#options'],
        '#value' => $exposed_form['sort_bef_combine']['#value'],
        '#attributes' => [
          'data-form-bind' => '.views-exposed-form',
          'data-bind' => 'sort_bef_combine',
          'data-submit-button-id' => $submit_button_id,
          'data-reset-button-id' => $reset_button_id,
          'data-reload-on-change' => ($reloadOnChange == '1') ? 'true' : 'false',
        ],
      ];

      // Set a label if showLabel is set & there a label set in the view.
      if (isset($exposed_form['sort_bef_combine']['#title'], $showLabel) && $showLabel === '1') {
        $markup[$filter_identifier]['#title'] = $exposed_form['sort_bef_combine']['#title'];
      }
    }

    // This is a sort field.
    if ($is_sort && !$is_bef_combined && in_array($filter, array_keys($view->sort))) {

      // This may need work later on / consistency with filters.
      $filter_identifier = $filter;

      // Get the combined sort options and render a select.
      $processed_options = $exposed_form['sort_order']['#options'];

      foreach ($processed_options as $key => $value) {
        $processed_options[$key] = $exposed_form['sort_by']['#options'][$filter_identifier] . ' (' . $value . ')';
      }

      $markup[$filter_identifier] = [
        '#type' => 'select',
        '#name' => $filter,
        '#options' => $processed_options,
        '#value' => $exposed_form['sort_order']['#value'],
        '#attributes' => [
          'data-form-bind' => '.views-exposed-form',
          'data-bind' => 'sort',
          'data-submit-button-id' => $submit_button_id,
          'data-reset-button-id' => $reset_button_id,
          'data-reload-on-change' => ($reloadOnChange == '1') ? 'true' : 'false',
        ],
      ];

      // Set a label if showLabel is set & there a label set in the view.
      if (isset($exposed_form['sort_by']['#title'], $showLabel) && $showLabel === '1') {
        $markup[$filter_identifier]['#title'] = $exposed_form['sort_by']['#title'];
      }

    }

    // This is a filter field.
    if (!$is_sort && in_array($filter, array_keys($view->filter))) {

      // Get the identifier.
      if (isset($view->filter[$filter]->options['expose']['identifier'])) {
        $filter_identifier = $view->filter[$filter]->options['expose']['identifier'];
      }

      // Make a copy of the existing element.
      $markup[$filter_identifier] = $exposed_form[$filter_identifier];

      // Set a label if showLabel is set & there a label set in the view.
      if (isset($exposed_form['#info']['filter-' . $filter]['label'], $showLabel) && $showLabel === '1') {
        $markup[$filter_identifier]['#title'] = $exposed_form['#info']['filter-' . $filter]['label'];
      }

      $markup[$filter_identifier]['#attributes']['data-form-bind'] = '.views-exposed-form';
      $markup[$filter_identifier]['#attributes']['data-bind'] = $markup[$filter_identifier]['#attributes']['data-form-bind'] . ' #' . $exposed_form[$filter_identifier]['#id'];
      $markup[$filter_identifier]['#attributes']['data-submit-button-id'] = $submit_button_id;
      $markup[$filter_identifier]['#attributes']['data-reset-button-id'] = $reset_button_id;
      $markup[$filter_identifier]['#attributes']['data-reload-on-change'] = ($reloadOnChange == '1') ? 'true' : 'false';

      $id = &$markup[$filter_identifier]['#id'];
      array_walk_recursive($markup[$filter_identifier], function ($value, $key) use (&$id) {
        if ($key === '#type' && ($value == 'checkbox' || $value == 'checkboxes' || $value == 'radio') && !empty($id)) {
          if (strpos($id, 'ssa-view-filter-')) {
            return;
          }
            $id = 'ssa-view-filter-' . $id;
        }
      });
      unset($markup[$filter_identifier]['#id']);
    }

    // If it's a list of items, possibly change the theme type
    // (<ul><li></ul> style).
    if ($display_type == 'list') {

      $list_markup = [];

      foreach ($markup[$filter_identifier]['#options'] as $key => $value) {
        $list_item_class = $filter ? 'ssa-' . $filter . '-' . $key : 'ssa-' . $key;

        $prefix = '<li class="' . $list_item_class . '">';
        if (isset($markup[$filter_identifier]['#value']) && ((is_array($markup[$filter_identifier]['#value']) && in_array($key, $markup[$filter_identifier]['#value'])) || (!is_array($markup[$filter_identifier]['#value']) && ($key == $markup[$filter_identifier]['#value'])))) {
          $prefix = '<li class="active ' . $list_item_class . '">';
        }

        $list_markup[] = [
          '#type' => 'html_tag',
          '#tag' => 'a',
          '#value' => $value,
          '#prefix' => $prefix,
          // Can't use nested tags - https://www.drupal.org/node/1488770
          '#suffix' => '</li>',
          // Can't use nested tags - https://www.drupal.org/node/1488770
          '#attributes' => [
            'href' => '#',
            'data-form-bind' => $markup[$filter_identifier]['#attributes']['data-form-bind'],
            'data-bind' => $markup[$filter_identifier]['#attributes']['data-bind'],
            'data-key' => $filter_identifier,
            'data-value' => $key,
            'data-submit-button-id' => $submit_button_id,
            'data-reset-button-id' => $reset_button_id,
            'data-reload-on-change' => ($reloadOnChange == '1') ? 'true' : 'false',
            'data-multiple' => !isset($markup[$filter_identifier]['#multiple']) || !$markup[$filter_identifier]['#multiple'] ? 'false' : 'true',
          ],
        ];
      }

      // Overwrite the form element style with the new array of <li>.
      $markup[$filter_identifier] = $list_markup;
    }

    // Return the render array for the modified form element.
    return $markup;
  }

  /**
   * Render a label for a view filter.
   *
   * This is only used when filter display type is "list", which is our custom
   * filter display. Label for "Drupal default" are added directly within
   * cohesionViewsExposed() twig function.
   *
   * @param $view
   * @param $filter
   * @param $viewDisplay
   *
   * @return mixed
   */
  public function cohesionViewsFilterLabel($view, $filter, $viewDisplay) {

    $loadedView = $this->entityTypeManager->getStorage('view')->load($view);
    $display = $loadedView->getDisplay($viewDisplay);

    // Are the filters set/overwritten on this display or just inherit
    // from the "default".
    if (!isset($display['display_options']['filters']) || !isset($display['display_options']['sorts'])) {
      $display = $loadedView->getDisplay('default');
    }

    // If it's a filter use the filter label set in the view, or it's a sort
    // so use the exposed sort label set in the view.
    return $display['display_options']['filters'][$filter]['expose']['label'] ?? $display['display_options']['exposed_form']['options']['exposed_sorts_label'];
  }

  /**
   * @param $view
   * @param $view_data
   *
   * @return array|mixed
   */
  public function cohesionViewsPagination($view, $view_data) {

    // Decode the data.
    if (!is_array($view_data)) {
      $view_data = Json::decode($view_data);
    }

    // Infinite scroll, force AJAX enabled.
    if (isset($view_data['settings']['pager']['view_pager']) && $view_data['settings']['pager']['view_pager'] == 'infinite_scroll') {
      $view->setAjaxEnabled(TRUE);
      views_views_pre_render($view);
    }

    // If pager is active.
    if ($view->display_handler->renderPager() && isset($view_data['settings']['pager']['view_pager']) && property_exists($view, 'pager')) {

      // Get the exposed input data (query string).
      $exposed_input = $view->exposed_raw_input ?? NULL;

      // Build the render array with exposed input (query string).
      $element = $view->pager->render($exposed_input);
      $variables = [
        'pager' => $element,
      ];

      // Get the output of the preprocessor functions.
      $theme_registry = $this->themeRegistry->getRuntime();
      $hook = $element['#theme'];

      if (is_array($hook)) {
        foreach ($hook as $candidate) {
          if ($theme_registry->has($candidate)) {
            break;
          }
        }
        $hook = $candidate;
      }

      $info = $theme_registry->get($hook);

      if (isset($info['variables'])) {
        foreach (array_keys($info['variables']) as $name) {
          if (isset($element["#$name"]) || array_key_exists("#$name", $element)) {
            $variables[$name] = $element["#$name"];
          }
        }
      }

      if (!empty($info['variables'])) {
        $variables += $info['variables'];
      }

      // Infinite scroll load automatically.
      if (isset($view_data['settings']['pager']['infinite']['loadAutomatically'])) {
        $variables['options']['automatically_load_content'] = $view_data['settings']['pager']['infinite']['loadAutomatically'];
      }

      if (isset($info['preprocess functions'])) {
        foreach ($info['preprocess functions'] as $preprocessor_function) {
          if (function_exists($preprocessor_function)) {
            $preprocessor_function($variables, $hook, $info);
          }
        }
      }

      // Return the variables to the cloned pagination twig.
      return $view_data + $variables;
    }
  }

  /**
   * Helper function for renderMenu (see below).
   *
   * @param $tree
   * @param $startLevel
   * @param $currentLevel
   *
   * @return mixed
   */
  private function yieldMenuAtLevel($tree, $startLevel, $currentLevel) {

    foreach ($tree as $item) {
      if ($item->inActiveTrail && isset($item->subtree)) {
        $currentLevel += 1;

        if ($startLevel === $currentLevel) {
          return $item->subtree;
        }
        else {
          return $this->yieldMenuAtLevel($item->subtree, $startLevel, $currentLevel);
        }
      }
    }
  }

  /**
   * @param $menu_name
   * @param $templateId
   * @param int $onlyRenderActiveTrail
   * @param int $startLevel
   *
   * @return array
   */
  public function renderMenu($menu_name, $templateId, $onlyRenderActiveTrail = 0, $startLevel = 1) {
    $menu_tree = \Drupal::menuTree();
    $menu = [];

    // Show the entire menu tree.
    if ($onlyRenderActiveTrail) {
      $parameters = $menu_tree->getCurrentRouteMenuTreeParameters($menu_name);
    }
    else {
      $parameters = new MenuTreeParameters();
      $parameters->setActiveTrail($menu_tree->getCurrentRouteMenuTreeParameters($menu_name)->activeTrail);
    }

    // Load the tree based on this set of parameters.
    $tree = $menu_tree->load($menu_name, $parameters);

    if ($startLevel > 1) {
      $tree = $this->yieldMenuAtLevel($tree, $startLevel, 1);

      // No items in the trail.
      if (!$tree) {
        $tree = [];
      }
    }

    try {
      // Transform the tree using the manipulators you want.
      $manipulators = [
        // Only show links that are accessible for the current user.
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        // Use the default sorting of menu links.
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $menu_tree->transform($tree, $manipulators);

      // Finally, build a renderable array from the transformed tree.
      $menu = $menu_tree->build($tree);

      $cache = [
        'tags' => ['cohesion.templates.' . $templateId],
        'contexts' => [],
      ];

      $candidate_template_storage = $this->entityTypeManager->getStorage('cohesion_menu_templates');
      $candidate_template = $candidate_template_storage->load($templateId);

      if ($candidate_template) {
        $cache['tags'] = array_merge($cache['tags'], $candidate_template->getCacheTags());
        $cache['contexts'] = array_merge($cache['contexts'], $candidate_template->getCacheContexts());

        $context_cache_metadata = \Drupal::service('cohesion_templates.context.cache_metadata');
        $context_names = $context_cache_metadata->extractContextNames($candidate_template);
        if (!empty($context_names)) {
          $context_cache = $context_cache_metadata->getContextsCacheMetadata($context_names);
          $cache['contexts'] = array_merge($cache['contexts'], $context_cache['contexts']);
          $cache['tags'] = array_merge($cache['tags'], $context_cache['tags']);
        }
      }

      // Add the cache tag (so menu is invalidated when the menu template
      // changes).
      $menu['#cache']['tags'] = array_merge($menu['#cache']['tags'], $cache['tags']);
      $menu['#cache']['contexts'] = array_merge($menu['#cache']['contexts'], $cache['contexts']);

      // Suggest the menu template.
      $menu['#theme'] = 'menu__cohesion_' . $templateId;
      $menu['#attributes'] = [];
    }
    catch (\Exception $e) {
      $this->loggerChannel->error($e->getTraceAsString());
    }

    // Return the output rendered menu.
    return $menu;
  }

  /**
   * Get menu link attributes from Drupal and extended modules.
   *
   * @param mixed $menu_link
   *
   * @return array
   */
  public function getMenuItemAttributes($menu_link) {
    // If the meu link passed was valid.
    if ($menu_link) {
      // Attempt to load the attributes (added by "menu_link_attributes" module
      // and others).
      $options = $menu_link->getOptions();

      if (isset($options['attributes'])) {
        // Add all attributes to 'all' array.
        $attributes['all'] = $options['attributes'];

        // Exclude class & target from being included in "custom"
        // attributes array.
        $exclude_attributes = ['class', 'target'];

        // Build a list of "custom" attributes that can be looped over and
        // added the menu link.
        foreach ($options['attributes'] as $key => $attribute) {
          if (!in_array($key, $exclude_attributes)) {
            $attributes['custom'][$key] = $attribute;
          }
        }

        return $attributes;

      }
    }

    return [];
  }

  /**
   * @param null $string
   *
   * @return int
   */
  public function cohCrc32($string = NULL) {
    if ($string) {
      return crc32($string);
    }
    return 0;
  }

  /**
   * @param null $string
   *
   * @return int
   */
  public function cohRaw($string = NULL) {
    if ($string) {
      return $string;
    }
    return '';
  }

  /**
   * Render a custom element.
   *
   * @param $elementSettings
   * @param $elementMarkup
   * @param $elementContext
   * @param $elementClassName
   *
   * @return mixed
   */
  public function customElement($elementSettings, $elementMarkup, $elementClassName, $elementContext = [], $elementChildren = '') {
    // Make TwigMarkup strings raw values.
    array_walk_recursive($elementSettings, function (&$value) {
      if ($value instanceof TwigMarkup) {
        $value = $value->__toString();
      }
    });

    // Fix component object key names.
    foreach ($elementSettings as $settings_key => $items) {
      if (is_array($items)) {
        foreach ($items as $key => $value) {
          unset($elementSettings[$settings_key][$key]);
          $elementSettings[$settings_key][str_replace('#', '', $key)] = $value;
        }
      }
    }

    // Fallback for v1 templates.
    if (is_object($elementSettings) || is_array($elementSettings)) {
      $elementSettings = json_encode($elementSettings, JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
    }

    // Render the custom element.
    $renderable = \Drupal::service('custom.elements')
      ->render($elementSettings, $elementMarkup, $elementClassName, $elementContext, $elementChildren);
    return $renderable;
  }

  /**
   * @return array of active trail breadcrumbs
   */
  public function getBreadCrumb() {
    $breadcrumb = \Drupal::service('breadcrumb')->build(\Drupal::routeMatch());
    $breadcrumbs = [];
    if (($breadcrumb instanceof Breadcrumb) && ($links = $breadcrumb->getLinks())) {
      foreach ($links as $link) {
        if ($link instanceof Link) {
          $breadcrumbs[] = [
            'text' => $link->getText(),
            'url' => $link->getUrl()->toString(),
          ];
        }
      }
    }
    return $breadcrumbs;
  }

  /**
   * @param $entity_info
   *
   * @return \Drupal\Core\GeneratedUrl|string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function pathRenderer($entity_info) {
    return $this->cohesionUtils->pathRenderer($entity_info);
  }

  /**
   * @param $wysiwyg
   * @param $text_format
   * @param $token_text
   *
   * @return \Drupal\Component\Render\MarkupInterface|string
   * @throws \Exception
   */
  public function formatWysiwyg($wysiwyg, $text_format, $token_text) {
    if ($wysiwyg != "" && $text_format instanceof TwigMarkup) {

      $wysiwyg_text = str_replace([
        '&lsqb;',
        '&rsqb;',
        '&lcub;',
        '&rcub;',
      ], ['[', ']', '{', '}'], $wysiwyg);
      $language = \Drupal::languageManager()->getCurrentLanguage()->getId();

      $build = [
        '#type' => 'processed_text',
        '#text' => $wysiwyg_text,
        '#format' => $text_format->__toString(),
        '#filter_types_to_skip' => [],
        '#langcode' => $language,
      ];

      return $this->renderer->render($build);
    }
    elseif ($wysiwyg == "" && $text_format == "" && $token_text instanceof TwigMarkup) {
      return Xss::filter($token_text->__toString());
    }

    return '';

  }

  /**
   * @return string of active trail breadcrumbs
   */
  public function getContentLanguage() {
    return \Drupal::languageManager()
      ->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)
      ->getId();
  }

  /**
   * Render an Entity.
   *
   * @param $entity_type
   * @param $view_mode
   * @param $entity_id
   *
   * @return array|string render a content
   */
  public function renderContent($entity_type, $view_mode, $entity_id) {
    try {
      if ($entity_type instanceof TwigMarkup) {
        $entity_type = $entity_type->__toString();
      }

      if ($view_mode instanceof TwigMarkup) {
        $view_mode = $view_mode->__toString();
      }

      if ($entity_id instanceof TwigMarkup) {
        $entity_id = $entity_id->__toString();
      }

      $view_mode_split = explode('.', $view_mode);
      if (isset($view_mode_split[1])) {
        $view_mode = $view_mode_split[1];
      }

      if (empty($view_mode)) {
        $view_mode = 'default';
      }

      if (!empty($entity_type) && !empty($view_mode) && !empty($entity_id)) {
        // Check if a valid UUID, fallback to ID to account for IDs being used
        // in tokens.
        if (Uuid::isValid($entity_id)) {
          // UUID given. Load entity by UUID.
          $results = $this->entityTypeManager
            ->getStorage($entity_type)
            ->loadByProperties(['uuid' => $entity_id]);
          $entity = reset($results);
        }
        else {
          // Entity ID given. Load entity as usual.
          $entity = $this->entityTypeManager
            ->getStorage($entity_type)
            ->load($entity_id);
        }

        // Get a translation based on the current context or
        // fallback to default.
        if ($entity instanceof EntityInterface) {
          $entity = $this->entityRepository->getTranslationFromContext($entity);
        }

        if ($entity instanceof EntityInterface && $entity->access('view', $this->currentUser)) {
          $access = $entity->access('view', $this->currentUser, TRUE);
          $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
          $cache_metadata = CacheableMetadata::createFromObject($access);

          if ($access instanceof AccessResultAllowed) {
            $build = $this->entityTypeManager->getViewBuilder($entity_type)->view($entity, $view_mode, $language);
          }

          else {
            $build = [
              "#cache" => [
                "contexts" => $entity->getCacheContexts(),
                "tags" => $entity->getCacheTags(),
                "max-age" => $entity->getCacheMaxAge(),
              ],
            ];
          }

          $build['#cache']['contexts'] = array_merge($build['#cache']['contexts'], $cache_metadata->getCacheContexts());
          $build['#cache']['tags'] = array_merge($build['#cache']['tags'], $cache_metadata->getCacheTags());

          return $build;
        }
      }

    }
    catch (\Exception $exception) {
      $this->loggerChannel->error($exception->getTraceAsString());
    }

    return '';
  }

  /**
   * @return bool has permission
   */
  public function hasDrupalPermission($permissions) {
    if (is_array($permissions)) {
      foreach ($permissions as $permission) {
        if (!$this->currentUser->hasPermission($permission)) {
          return FALSE;
        }
      }
    }

    return TRUE;
  }

  /**
   * @return string esacped url
   */
  public function escapeURL($url) {
    return UrlHelper::stripDangerousProtocols($url);
  }

  /**
   *
   */
  public function getComponentFieldValue($context, $key, $sub_key_path = NULL) {
    $keys = [];
    // If we need to find the find further down the array
    if (is_string($sub_key_path) && !empty($sub_key_path)) {
      $keys = array_merge($keys, explode(',', $sub_key_path));
    }

    $value = FALSE;

    // If within the context of a field repeater check if the key exists
    // otherwise check in the component field values
    if (isset($context['coh_repeater_val'])) {
      if (isset($context['coh_repeater_val']['#' . $key])) {
        $value = $context['coh_repeater_val']['#' . $key];
      }
    }
    elseif (isset($context['componentFieldsValues'][$key])) {
      $value = $context['componentFieldsValues'][$key];
    }
    elseif (isset($context['componentFieldsValues'])) {
      // Try to find the key in all componentFieldsValues
      // If this is a field inside a field repeater but used outside a pattern
      // repeater. Use case: Hide if no data
      foreach ($context['componentFieldsValues'] as $cpt_value) {
        if (is_array($cpt_value)) {
          foreach ($cpt_value as $repeater_value) {
            if (is_array($repeater_value) && isset($repeater_value['#' . $key])) {

              $value = $repeater_value['#' . $key];
              foreach ($keys as $key) {
                if (is_array($value) && isset($value[$key])) {
                  $value = $value[$key];
                }
                else {
                  $value = '';
                }
              }

              if (is_string($value) && trim($value) !== '') {
                return $value;
              }
            }
          }
        }
      }
      return FALSE;
    }

    foreach ($keys as $key) {
      if (is_array($value) && isset($value[$key])) {
        $value = $value[$key];
      }
      else {
        return FALSE;
      }
    }

    return $value;
  }

  /**
   *
   */
  public function setViewIterate(&$context) {
    $iterate = &drupal_static('coh_view_itertate');
    $iterate = 0;
  }

  /**
   *
   */
  public function getViewIterate(&$context) {
    $iterate = &drupal_static('coh_view_itertate', 0);
    return $iterate;
  }

  /**
   *
   */
  public function incrementViewIterate(&$context) {
    $iterate = &drupal_static('coh_view_itertate', 0);
    $iterate = $iterate + 1;
  }

  /**
   * @param $context
   * @param $value
   * @return int|mixed|string
   */
  public function renderFieldUuid($context, $value) {
    // Get the component instance and entity from context.
    $instance_uuid = $context['componentUuid'];
    $entity = $context['layout_builder_entity']['entity'] ?? '';

    if ($entity) {
      // Get the layout canvas
      if ($layout_canvas = $entity->getLayoutCanvasInstance()) {
        // Loop over layout canvas.
        foreach ($layout_canvas->iterateCanvas() as $element) {
          // Check the current element we're iterating over to see if
          // it's the current component instance.
          if ($element->isComponent() && $instance_uuid === $element->getUUID()) {
            // If a pattern repeater exists return
            // repeaterUUID_repeaterIndex_fieldUUID.
            if (isset($context['coh_repeater_val']) || isset($context['_seq']) && isset($context['_key'])) {
              foreach ($element->getModel()->getValues() as $repeater_key => $element_model_values) {
                if (is_array($element_model_values)) {
                  $index = str_replace('#', '', $context['_key']);
                  foreach ($element_model_values as $element_model_value) {
                    foreach ($element_model_value as $field_key => $field_value) {
                      if ($field_key === $value) {
                        // Get repeater "index" from the context.
                        // Return the UUID as React expects.
                        return $repeater_key . '_' . $index . '_' . $field_key;
                      }
                    }
                  }
                  return $this->fetchNestedComponentFieldUuid($element, $value, $repeater_key, $index);
                }
              }
            }
            return $this->fetchNestedComponentFieldUuid($element, $value);
          }
        }
      }
    }
    return $value;
  }

  /**
   *  Fetch nested components field UUID for in place editing.
   * @param $element
   * @param $value
   * @param $repeater_key
   * @param $index
   * @return mixed|string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function fetchNestedComponentFieldUuid($element, $value, $repeater_key = NULL, $index = NULL) {
    // Get the component & canvas model.
    if ($component = $this->entityTypeManager->getStorage('cohesion_component')->load($element->getComponentID())) {
      if ($component_model = $component->getLayoutCanvasInstance()->iterateModels('canvas')) {
        // Loop over the component modal.
        foreach ($component_model as $model) {
          foreach ($model->getValues() as $model_key => $model_value) {
            // Check the key & field UUID passed in match.
            if ($model_key === $value && is_string($model_value)) {
              // Check that the model value matches a field value.
              if (preg_match('/(\(|\[)((field).)(([0-9a-f]{7,8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12})|(([\s\S]*?)\/([\s\S]*?)\/([\s\S]*?)))(\)|\])/', $model_value, $matches)) {
                // Return the match of the UUID from the field value.
                if (isset($repeater_key) && isset($index)) {
                  return $repeater_key . '_' . $index . '_' . $matches[4];
                } else {
                  return $matches[4];
                }
              }
            }
          }
        }
      }
    }

    return $value;
  }

  /**
   * Only attach styles that are required rather than globally load.
   */
  public function renderStyles($entityId, $entityTypeId) {
    $elementStylesLoad = $this->cohesionUtils->loadElementStylesOnPageOnly();

    try {
      $entityTypeStorage = $this->entityTypeManager->getStorage($entityTypeId);
      $entity = $entityTypeStorage->load($entityId);
    }
    catch (\Exception $exception) {
      $this->loggerChannel->error($exception->getMessage(), Error::decodeException($exception));
    }

    if (!empty($entity)) {
      $librariesToAttach = [];

      if ($entity instanceof CohesionLayout) {
        $entity = $entity->getParentEntity();
      }

      // Check if element styles should be loaded as separate libraries?
      if ($elementStylesLoad) {
        $librariesToAttach[] = 'cohesion/' . $entityTypeId . '_' . $entityId;
      }

      /** @var \Drupal\cohesion\UsageUpdateManager $usageUpdateManager */
      $customStylesUsage = $this->usageUpdateManager->getEntitiesInUseInSource($entity, 'cohesion_custom_style');

      $customStylesToLoad = [];
      if (!empty($customStylesUsage)) {
        $customStyleStorage = $this->entityTypeManager->getStorage('cohesion_custom_style');

        foreach (array_keys($customStylesUsage) as $customStylesUuid) {
          $customStyles = $customStyleStorage->loadByProperties(['uuid' => $customStylesUuid]);
          $customStyle = array_shift($customStyles);
          $customStylesToLoad[] = $customStyle->id();
        }
      }

      // Check if custom styles should be loaded as separate libraries?
      if ($customStylesToLoad) {
        foreach ($customStylesToLoad as $customStyleToLoad) {
          $libraryName = 'cohesion/coh_custom_style_' . $customStyleToLoad;
          $librariesToAttach[] = $libraryName;
        }
      }

      // Check if custom styles or custom element styles should be loaded
      // as separate libraries?
      if ($customStylesToLoad || $elementStylesLoad) {
        $templateAttached = [
          '#attached' => [
            'library' => $librariesToAttach,
          ],
        ];

        return $this->renderer->render($templateAttached);
      }
    }

    return '';
  }

}
