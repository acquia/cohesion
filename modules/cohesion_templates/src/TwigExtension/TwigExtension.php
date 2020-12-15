<?php

namespace Drupal\cohesion_templates\TwigExtension;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Component\Render\HtmlEscapedText;
use Drupal\Component\Serialization\Json;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Link;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Markup;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\Core\Template\TwigEnvironment;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Utility\Token;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface;
use Twig\Markup as TwigMarkup;

/**
 * Class TwigExtension.
 *
 * @package Drupal\cohesion_templates\TwigExtension
 */
class TwigExtension extends \Twig_Extension {

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
   * @var \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface
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
   * TwigExtension constructor.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   * @param \Drupal\Core\Utility\Token $token
   * @param \Drupal\Core\Theme\Registry $themeRegistry
   * @param \Drupal\Core\Template\TwigEnvironment $twigEnvironment
   * @param \Drupal\Component\Uuid\UuidInterface $uuid
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   * @param \Symfony\Component\HttpFoundation\File\MimeType\MimeTypeGuesserInterface $extension_mime_type_guesser
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
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
    LoggerChannelFactoryInterface $loggerChannelFactory
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
      new \Twig_SimpleFunction('processtoken', [$this, 'tokenReplace']),
      new \Twig_SimpleFunction('renderComponent', [$this, 'renderComponent']),
      new \Twig_SimpleFunction('renderInlineStyle', [$this, 'renderInlineStyle']),
      new \Twig_SimpleFunction('drupal_block', [$this, 'drupalBlock']),
      new \Twig_SimpleFunction('contextpasses', [$this, 'contextPasses']),
      new \Twig_SimpleFunction('drupal_view_item', [$this, 'drupalViewItem']),
      new \Twig_SimpleFunction('node_path', [$this, 'nodePath']),
      new \Twig_SimpleFunction('cohesion_image_style', [
        $this,
        'cohesionImageStyle',
      ]),
      new \Twig_SimpleFunction('cohesion_image_mime_type', [
        $this,
        'cohesionImageMimeType',
      ]),
      new \Twig_SimpleFunction('cohesion_views_exposed', [
        $this,
        'cohesionViewsExposed',
      ]),
      new \Twig_SimpleFunction('cohesion_views_pagination', [
        $this,
        'cohesionViewsPagination',
      ]),
      new \Twig_SimpleFunction('render_menu', [$this, 'renderMenu']),
      new \Twig_SimpleFunction('customElement', [$this, 'customElement']),
      new \Twig_SimpleFunction('coh_instanceid', [$this, 'cohInstanceId']),
      new \Twig_SimpleFunction('printr', [$this, 'printr']),
      new \Twig_SimpleFunction('cohesion_breadcrumb', [$this, 'getBreadCrumb']),
      new \Twig_SimpleFunction('get_menu_item_attributes', [
        $this,
        'getMenuItemAttributes',
      ]),
      new \Twig_SimpleFunction('range_test', [$this, 'rangeTest']),
      new \Twig_SimpleFunction('castToString', [$this, 'castToString']),
      new \Twig_SimpleFunction('path_renderer', [$this, 'pathRenderer']),
      new \Twig_SimpleFunction('format_wysiwyg', [$this, 'formatWysiwyg']),
      new \Twig_SimpleFunction('renderContent', [$this, 'renderContent']),
      new \Twig_SimpleFunction('get_content_language', [
        $this,
        'getContentLanguage',
      ]),
      new \Twig_SimpleFunction('patchYoutubeUrl', [$this, 'patchYoutubeUrl']),
      new \Twig_SimpleFunction('has_drupal_permission', [
        $this,
        'hasDrupalPermission',
      ]),
      new \Twig_SimpleFunction('escape_url', [$this, 'escapeURL']),
      new \Twig_SimpleFunction('isActiveTheme', [$this, 'isActiveTheme']),
      new \Twig_SimpleFunction('getComponentFieldValue', [$this, 'getComponentFieldValue'], ['needs_context' => TRUE]),
      new \Twig_SimpleFunction('setViewIterate', [$this, 'setViewIterate'], ['needs_context' => TRUE]),
      new \Twig_SimpleFunction('getViewIterate', [$this, 'getViewIterate'], ['needs_context' => TRUE]),
      new \Twig_SimpleFunction('incrementViewIterate', [$this, 'incrementViewIterate'], ['needs_context' => TRUE]),
    ];
  }

  /**
   * Generates a list of all Twig filters that this extension defines.
   */
  public function getFilters() {
    return [
      new \Twig_SimpleFilter('int', [$this, 'parseInt']),
      new \Twig_SimpleFilter('crc32', [$this, 'cohCrc32']),
      new \Twig_SimpleFilter('clean', [$this, 'clean']),
      new \Twig_SimpleFilter('coh_raw', [$this, 'cohRaw']),
    ];
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
    if (preg_match('/^.*(?:(?:youtu\.be\/|v\/|vi\/|u\/\w\/|embed\/)|(?:(?:watch)?\?v(?:i)?=|\&v(?:i)?=))([^#\&\?]*).*/', $string, $matches)) {
      return "https://www.youtube.com/embed/" . $matches[1];
    }
    return 'https://www.youtube.com/embed/';
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
   * Used for tokenized inline styles.
   */
  public function cohInstanceId() {
    return 'coh-instance-' . crc32($this->uuid->generate());
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
    if (key($data) == 'menu_link_content') {
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
            if ($entity = \Drupal::service('entity.repository')
              ->loadEntityByUuid($def[0], $def[1])) {

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
    ], new BubbleableMetadata());

    // If layout canvas, decode entities as component will take care of escaping.
    if (!$isTemplate) {
      $token_replacement = Html::decodeEntities($token_replacement);
    }

    return Markup::create($token_replacement);
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
    if ($componentContentId && is_numeric($componentContentId) && (!isset($_context['component_content']) || $_context['component_content'] instanceof ComponentContent && $_context['component_content']->id() !== $componentContentId)) {
      /** @var \Drupal\cohesion_elements\Entity\ComponentContent $componentContent */
      $componentContent = ComponentContent::load($componentContentId);
      if ($componentContent && $componentContent->isPublished()) {
        $view_builder = $this->entityTypeManager->getViewBuilder('component_content');
        $build = $view_builder->view($componentContent);
        return $this->renderer->render($build);
      }
      return [];
    }

    // First, check the component actually exists.
    // @TODO this can probably be moved further down and in buildComponentFields and would benefit from a loadMultiple
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

      $contextual_content = [];
      // Set up contextual links.
      if (isset($_context['layout_builder_entity'])) {

        if (!isset($_context['layout_builder_entity']['entity']) || !$_context['layout_builder_entity']['entity'] instanceof CohesionLayout) {
          $cohesion_layout = $this->entityTypeManager->getStorage($_context['layout_builder_entity']['entity_type_id'])
            ->loadRevision($_context['layout_builder_entity']['revision_id']);
        }
        else {
          $cohesion_layout = $_context['layout_builder_entity']['entity'];
        }

        if ($_context['layout_builder_entity']['id'] !== NULL) {
          if ($cohesion_layout instanceof CohesionLayout) {
            if ($cohesion_layout->get('parent_type')->value == 'component_content') {
              // Add Edit component content contextual link.
              $contextual_content['#contextual_links']['cohesion_component_content_edit'] = [
                'route_parameters' => [
                  'component_content' => $cohesion_layout->get('parent_id')->value,
                ],
              ];
            }
            else {
              // Add Edit component contextual link.
              $contextual_content['#contextual_links']['cohesion_elements'] = [
                'route_parameters' => [
                  'entity_type_id' => $_context['layout_builder_entity']['entity_type_id'],
                  'component_id' => $componentId,
                  'id' => $_context['layout_builder_entity']['id'],
                  'revision_id' => $_context['layout_builder_entity']['revision_id'],
                  'uuid' => $componentInstanceUuid,
                  'randomizer' => $this->uuid->generate(),
                ],
              ];
            }
          }
        }

        // Force a new 'data-contextual-id' attribute on blocks when this module is
        // enabled so as not to reuse stale data cached client-side.
        // @todo Remove when https://www.drupal.org/node/2773591 is fixed.
        $contextual_content['#contextual_links']['settings_tray'] = [
          'route_parameters' => [],
        ];
      }

      $contextual_content['#contextual_links']['cohesion_configuration'] = [
        'route_parameters' => [
          'cohesion_component' => $componentId,
        ],
      ];

      // Build the render array.
      $cacheContexts = \Drupal::service('cohesion_templates.cache_contexts');
      $cache_contexts = $cacheContexts->getFromTemplateEntityId($component, $componentFieldsValues);

      $template = 'component__cohesion_' . str_replace('-', '_', $componentId);

      return [
        '#theme' => $template,
        '#content' => isset($contextual_content) ? $contextual_content : '',
        '#cache' => [
          'tags' => ['component.cohesion.' . $componentId],
          'contexts' => $cache_contexts,
        ],
        '#parentContext' => $_context,
        // This is used inside preprocess_cohesion_elements_component()
        '#parentIsComponent' => TRUE,
        // Tell children elements that its parent is a component (for components in component)
        '#componentFieldsValues' => $componentFieldsValues,
        '#componentUuid' => $componentInstanceUuid,
        '#template' => $template,
      ];
    }
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
        // If the key on the values exists in the model then it's a multifield value.
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
      // Escape only once at the top most parent component, child component (component in component)
      // should not escape again, also the model in a component is considered secure as it is done by
      // site builder and they should have as much power as possible.
      if (!$isTemplate || !empty($component_model[$uuid]->getProperty(['settings', 'toggleType']))) {
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
    if (is_object($id) && $id instanceof \Twig_Markup) {
      $id = $id->__toString();
    }

    $block = $this->entityTypeManager->getStorage('block')->load($id);
    if ($block && $block->access('view')) {
      return $this->entityTypeManager->getViewBuilder('block')->view($block);
    }
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
        $contexts[] = isset($context_data[1]) ? $context_data[1] : $context_data[0];
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
        elseif ($condition == 'OR' && empty($active_contexts)) {
          return FALSE;
        }
      }
    }

    // Get active contexts on this page.
    return TRUE;
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
    if (is_array($rows) && isset($rows['#rows'])) {

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
          // Add view modes for the current entity type per bundle to an key value array.
          foreach ($view_modes as $view_mode_data) {
            // Cast any Twig_Markup values to strings.
            foreach ($view_mode_data as $view_mode_data_key => $view_mode_data_value) {
              if (is_object($view_mode_data_value) && $view_mode_data_value instanceof \Twig_Markup) {
                $view_mode_data[$view_mode_data_key] = $view_mode_data_value->__toString();
              }
            }

            // We only register defined view modes for the entity type of the current entity
            // We don't register view mode set as default as it is already in the resultrow array.
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

          // Change the view mode served by the view to the view mode specified in Cohesion
          // If none exists (for the bundle or if no `all` has been specified) it will fall
          // back the one in the Drupal view.
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
      $alias = \Drupal::service('path.alias_manager')
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
    // If created as a component field, the image style will come as Twig_Markup.
    if (is_object($image_style) && $image_style instanceof \Twig_Markup) {
      $image_style = $image_style->__toString();
    }

    $file_uri = html_entity_decode(stripcslashes($file_uri));
    if (!\Drupal::service('stream_wrapper_manager')->isValidUri($file_uri)) {
      return $file_uri;
    }

    // Try and load the given image style.
    try {
      if ($image_style != '') {
        if ($is = ImageStyle::load($image_style)) {
          $url = $is->buildUrl($file_uri);
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

    // No image style or error, so just render the full image.
    $url = file_create_url($file_uri);
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
    // If created as a component field, the image style will come as Twig_Markup.
    if (is_object($image_style) && $image_style instanceof \Twig_Markup) {
      $image_style = $image_style->__toString();
    }

    $file_uri = html_entity_decode(stripcslashes($file_uri));

    $extension = pathinfo($file_uri, PATHINFO_EXTENSION);
    $fake_path = 'dx8_image.' . $extension;
    if ($image_style != '') {
      if ($image_style_entity = ImageStyle::load($image_style)) {
        $fake_path = 'dx8_image.' . $image_style_entity->getDerivativeExtension($extension);
      }
    }

    return $this->extensionMimeTypeGuesser->guess($fake_path);
  }

  /**
   * @param $view
   * @param $filter
   * @param $display_type
   *
   * @return array
   */
  public function cohesionViewsExposed($view, $filter, $display_type, $reloadOnChange) {

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

    // Sort filter may have the same id as filter id so they are prefixed bu coh_sort_.
    $is_sort = FALSE;
    if (strpos($filter, 'coh_sort_') === 0) {
      $filter = substr($filter, strlen('coh_sort_'));
      $is_sort = TRUE;
    }

    // This is a sort field.
    if ($is_sort && in_array($filter, array_keys($view->sort))) {

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
    }

    // This is a filter field.
    if (!$is_sort && in_array($filter, array_keys($view->filter))) {

      // Get the identifier.
      if (isset($view->filter[$filter]->options['expose']['identifier'])) {
        $filter_identifier = $view->filter[$filter]->options['expose']['identifier'];
      }

      // Make a copy of the existing element.
      $markup[$filter_identifier] = $exposed_form[$filter_identifier];
      $markup[$filter_identifier]['#attributes']['data-form-bind'] = '.views-exposed-form';
      $markup[$filter_identifier]['#attributes']['data-bind'] = $markup[$filter_identifier]['#attributes']['data-form-bind'] . ' #' . $exposed_form[$filter_identifier]['#id'];
      $markup[$filter_identifier]['#attributes']['data-submit-button-id'] = $submit_button_id;
      $markup[$filter_identifier]['#attributes']['data-reset-button-id'] = $reset_button_id;
      $markup[$filter_identifier]['#attributes']['data-reload-on-change'] = ($reloadOnChange == '1') ? 'true' : 'false';
      unset($markup[$filter_identifier]['#id']);
      foreach ($markup[$filter_identifier] as &$attribute) {
        if (is_array($attribute) && isset($attribute['#type']) && ($attribute['#type'] == 'checkbox' || $attribute['#type'] == 'radio') && isset($attribute['#id'])) {
          $attribute['#id'] = 'coh-view-filter-' . $attribute['#id'];
        }
      }
    }

    // If it's a list of items, possibly change the theme type (<ul><li></ul> style).
    if ($display_type == 'list') {

      $list_markup = [];

      foreach ($markup[$filter_identifier]['#options'] as $key => $value) {
        $list_item_class = $filter ? 'coh-' . $filter . '-' . $key : 'coh-' . $key;

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
      $exposed_input = isset($view->exposed_raw_input) ? $view->exposed_raw_input : NULL;

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

      // Add the cache tag (so menu is invalidated when the menu template changes).
      $menu['#cache']['tags'][] = 'cohesion.templates.' . $templateId;

      $cacheContexts = \Drupal::service('cohesion_templates.cache_contexts');
      if (!isset($menu['#cache']['contexts'])) {
        $menu['#cache']['contexts'] = [];
      }

      $candidate_template_storage = $this->entityTypeManager->getStorage('cohesion_menu_templates');
      $candidate_template = $candidate_template_storage->load($templateId);
      $menu['#cache']['contexts'] = array_merge($menu['#cache']['contexts'], $cacheContexts->getFromTemplateEntityId($candidate_template));

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
      // Attempt to load the attributes (added by "menu_link_attributes" module and others).
      $options = $menu_link->getOptions();

      if (isset($options['attributes'])) {
        return $options['attributes'];
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
  public function customElement($elementSettings, $elementMarkup, $elementClassName, $elementContext = []) {
    // Make TWig_Markup strings raw values.
    array_walk_recursive($elementSettings, function (&$value) {
      /** @var \Twig_Markup|mixed $value */
      if (is_object($value) && $value instanceof \Twig_Markup) {
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
      ->render($elementSettings, $elementMarkup, $elementClassName, $elementContext);
    return render($renderable);
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
    if ($wysiwyg != "" && $text_format instanceof \Twig_Markup) {

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
    elseif ($wysiwyg == "" && $text_format == "" && $token_text instanceof \Twig_Markup) {
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
      if ($entity_type instanceof \Twig_Markup) {
        $entity_type = $entity_type->__toString();
      }

      if ($view_mode instanceof \Twig_Markup) {
        $view_mode = $view_mode->__toString();
      }

      if ($entity_id instanceof \Twig_Markup) {
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
        $results = $this->entityTypeManager->getStorage($entity_type)->loadByProperties(['uuid' => $entity_id]);
        $entity = reset($results);

        if ($entity) {
          $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
          $view_builder = $this->entityTypeManager->getViewBuilder($entity_type);
          $build = $view_builder->view($entity, $view_mode, $language);
          return $this->renderer->render($build);
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
        if (!\Drupal::currentUser()->hasPermission($permission)) {
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
    if (isset($context['coh_repeater_val']['#' . $key])) {
      $value = $context['coh_repeater_val']['#' . $key];
    }
    elseif (isset($context['componentFieldsValues'][$key])) {
      $value = $context['componentFieldsValues'][$key];
    }
    else {
      return FALSE;
    }

    if (is_string($sub_key_path) && !empty($sub_key_path)) {
      $keys = array_merge($keys, explode(',', $sub_key_path));
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

}
