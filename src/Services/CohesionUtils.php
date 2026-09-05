<?php

namespace Drupal\cohesion\Services;

use Drupal\cohesion_elements\Entity\Component;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Drupal\Core\Utility\Error;
use Drupal\file\FileInterface;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

/**
 * Generic helper functions
 *
 * @package Drupal\cohesion
 */
class CohesionUtils {

  const SCHEMES = ['http', 'https', '/'];

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Logger Channel Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * Custom Components Discovery service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponentsService = NULL;

  /**
   * Front end settings config.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $frontEndSettings;

  /**
   * Configuration Factory Interface.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * CohesionUtils constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   Theme handler.
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   *   Theme manager.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   Language manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   */
  public function __construct(
    ThemeHandlerInterface $theme_handler,
    ThemeManagerInterface $theme_manager,
    EntityTypeManagerInterface $entity_type_manager,
    LanguageManagerInterface $language_manager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    ConfigFactoryInterface $configFactory,
    ModuleHandlerInterface $moduleHandler,
  ) {
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
    $this->entityTypeManager = $entity_type_manager;
    $this->languageManager = $language_manager;
    $this->loggerChannelFactory = $loggerChannelFactory;
    $this->configFactory = $configFactory;
    $this->moduleHandler = $moduleHandler;
    $this->frontEndSettings = $this->configFactory->getEditable('cohesion.frontend.settings');

    if ($this->moduleHandler->moduleExists('cohesion_elements')) {
      try {
        $this->customComponentsService = \Drupal::service('custom.components');
      } catch (\Exception $exception) {
        $this->loggerChannelFactory->get('cohesion_elements.custom_components')->error($exception->getMessage(), Error::decodeException($exception));
      }
    }
  }

  /**
   * Handles errors in different ways depending on the state of the application.
   *
   * @param $message
   * @param $force_exception
   *
   * @throws \Exception
   */
  public function errorHandler($message, $force_exception = FALSE) {
    // Always send the error to dblog.
    $this->loggerChannelFactory->get('cohesion')->error($message);

    // If part of a batch process, always throw an exception.
    if (\Drupal::config('cohesion.settings')->get('fail.on.error') || $force_exception) {
      $running_dx8_batch = &drupal_static('running_dx8_batch');
      if ($running_dx8_batch || $force_exception) {
        throw new \Exception($message);
      }
      // If outside of a batch process, warn the user.
      else {
        \Drupal::messenger()->addMessage($message, 'error');
      }
    }
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
   * @param \Drupal\Core\Extension\Extension $theme
   *
   * @return bool
   */
  private function isThemeCohesionEnabled($theme): bool {
    return isset($theme->info['cohesion']) && $theme->info['cohesion'] === TRUE;
  }

  /**
   * Get all enabled theme with Site Studio enabled.
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
   *
   */
  public function getCohesionTemplateOnlyEnabledThemes() {
    $themes = [];
    foreach ($this->themeHandler->listInfo() as $theme_info) {
      if (theme_get_setting('features.layout_canvas_field', $theme_info->getName())) {
        $themes[] = $theme_info->getName();
      }
    }
    return $themes;
  }

  /**
   * Returns whether a theme has cohesion enabled, it can be its parent(s)
   *
   * @param $theme_id
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
   * @todo store as a static.
   */
  public function usedx8Status() {
    $dx8_config = \Drupal::config('cohesion.settings');
    if (!$dx8_config || $dx8_config->get('use_dx8') === 'disable' || !$dx8_config->get('api_key') || $dx8_config->get('api_key') == '') {
      return FALSE;
    }
    return TRUE;
  }

  /**
   * Check if Site Studio API keys are configured.
   *
   * @return bool
   *   TRUE if both API key and organization key are set, FALSE otherwise.
   */
  public function apiKeysConfigured(): bool {
    $config = $this->configFactory->get('cohesion.settings');
    $api_key = trim((string) $config->get('api_key'));
    $organization_key = trim((string) $config->get('organization_key'));

    return $api_key !== '' && $organization_key !== '';
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

              $this->moduleHandler->alter('dx8_' . $context . '_drupal_token_context', $context_variable);

              // If token has been detected replace potential breaking chars
              // with nothing as they are not valid.
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

  /**
   * @param $fieldValue
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $model
   * @param null $default
   *
   * @return \Drupal\Core\GeneratedUrl|false|mixed|string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function processFieldValues($fieldValue, $model, $default = NULL) {
    if (!$model->getProperty(['settings', 'type'])) {
      $schema_type = $model->getProperty(['settings', 'schema', 'type']);
      $escape = $model->getProperty(['settings', 'schema', 'escape']);
      if ($schema_type === 'string' && (is_null($escape) || $escape === TRUE)) {
        $fieldValue = Html::escape($fieldValue);
      }
    }
    else {
      $escape = $model->getProperty(['settings', 'schema', 'escape']);
      switch ($model->getProperty(['settings', 'type'])) {
        case 'checkboxToggle':
          $toggle_type = $model->getProperty(['settings', 'toggleType']);
          if ($toggle_type == 'string'|| $toggle_type == 'number') {
            $true_value = $model->getProperty(['settings', 'trueValue']);
            $false_value = $model->getProperty(['settings', 'falseValue']);
            if ($fieldValue && $true_value) {
              $fieldValue = $true_value;
            }
            elseif (!$fieldValue && $false_value) {
              $fieldValue = $false_value;
            }
            else {
              $fieldValue = '';
            }
          }
          break;

        case 'cohTextarea':
          if (is_null($escape) || $escape === TRUE) {
            $fieldValue = Html::escape($fieldValue);
          }
          break;

        case 'cohSelect':
          $fieldValue = strval($fieldValue);

          // Is the value in the endpoint based select options.
          if ($model->getProperty(['settings', 'selectType']) == 'existing') {
            // Really this should look up the values in the endpoint,
            // but it's not possible to call the endpoint
            // get the valued programmatically.
            // This is some protection.
            $fieldValue = Xss::filter($fieldValue);
          }
          // Is the value in the manually predefined select options.
          else {
            // Skip validation as it's an external source.
            if (in_array($model->getProperty(['settings', 'selectType']), ['external', 'function'])) {
              break;
            }

            $is_in_select = FALSE;
            foreach ($model->getProperty(['settings', 'options']) as $option) {
              if (property_exists($option, 'value') && $fieldValue == $option->value) {
                $is_in_select = TRUE;
                break;
              }
            }

            // In not in the select options fallback to default value.
            if (!$is_in_select) {
              if ($default) {
                $fieldValue = $default;
              }
              else {
                $fieldValue = '';
              }
            }
          }
          break;

        case 'cohWysiwyg':
          break;

        case 'cohTypeahead':
          $fieldValue = $this->urlProcessor($fieldValue);
          break;

        default:
          if (is_string($fieldValue) || is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
            $content = json_decode($fieldValue);
            if (is_null($escape) || $escape) {
              if ($content !== NULL && (is_object($content) || is_array($content))) {
                $fieldValue = json_encode($this->escapeJson($content));
              }
              else {
                $fieldValue = Html::escape($fieldValue);
              }
            }
          }
          else {
            $fieldValue = $this->escapeJson($fieldValue);
          }
          break;
      }
    }

    return $fieldValue;
  }

  /**
   * Given an url ensure it's valid & encoded correctly or return empty string.
   *
   * @param string $url
   * @return \Drupal\Core\GeneratedUrl|string
   */
  public function urlProcessor(string $url, $absolute = FALSE) {
    // Encode path to account for spaces in external and relative paths, skip
    // encoding of Drupal internal links & mailto & other link types.
    if ($this->checkUrlStartsWith($url)) {

      // First check if the URL given has already been encoded, if so return
      // the url, so it's not encoded twice.
      if (preg_match("@^[a-zA-Z0-9%+-_]*$@", $url)) {
        return $url;
      }

      $parsedUrl = parse_url($url);
      $scheme = isset($parsedUrl['scheme']) ? $parsedUrl['scheme'] . '://' : NULL;
      $host = $parsedUrl['host'] ?? '';
      $path = isset($parsedUrl['path']) ? UrlHelper::encodePath($parsedUrl['path']) : '';
      $query = isset($parsedUrl['query']) ? '?' . $parsedUrl['query'] : '';
      $fragment = isset($parsedUrl['fragment']) ? '#' . $parsedUrl['fragment'] : '';

      // Is it an absolute URL?
      if (isset($scheme)) {
        $absolute = TRUE;
      }

      $url = $scheme . $host . $path . $query . $fragment;
    }
    else {
      try {
        $url = $this->pathRenderer($url, $absolute);
      } catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
        $this->loggerChannelFactory->get('cohesion')->error($e->getMessage());
      }
    }

    // Check that the URL is valid
    // accounts for node::1, mailto:, external & internal links.
    if (!UrlHelper::isValid($url, $absolute)) {
      $url = '';
    }
    return $url;
  }

  /**
   * @param string $url
   * @return bool
   */
  private function checkUrlStartsWith(string $url): bool {

    foreach (self::SCHEMES as $scheme) {
      if (strpos($url, $scheme) === 0) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * @param $json
   *
   * @return array|object|string|null
   */
  private function escapeJson($json) {

    $escaped = NULL;

    if (is_object($json)) {
      $escaped = new \stdClass();
      foreach ($json as $key => $value) {
        $escaped->{Html::escape($key)} = $this->escapeJson($value);
      }
    }
    elseif (is_array($json)) {
      $escaped = [];
      foreach ($json as $key => $value) {
        $escaped[Html::escape($key)] = $this->escapeJson($value);
      }
    }
    elseif (is_string($json)) {
      $escaped = Html::escape($json);
    }
    else {
      $escaped = $json;
    }

    return $escaped;
  }

  /**
   * @param $entity_info
   *
   * @return \Drupal\Core\GeneratedUrl|string
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function pathRenderer($entity_info, $absolute = FALSE) {
    $entity_data = explode('::', (string) $entity_info);

    if (count($entity_data) > 1) {
      switch ($entity_data[0]) {
        case 'media':
          if (isset($entity_data[1])) {
            $media_storage = $this->entityTypeManager->getStorage('media');
            if ($media = $media_storage->load($entity_data[1])) {
              if (\Drupal::config('cohesion.settings')->get('link_element_media')) {
                $source = $media->getSource();
                $configuration = $source->getConfiguration();
                $source_field = $configuration['source_field'] ?? NULL;
                if ($source_field && $media->hasField($source_field) && !$media->get($source_field)->isEmpty()) {
                  // Check for Acquia DAM assets first.
                  if (str_starts_with($source->getPluginId(), 'acquia_dam_asset')) {
                    $source_value = $source->getSourceFieldValue($media);
                    if (isset($source_value['asset_id'], $source_value['version_id'])) {
                      $uri = "acquia-dam://{$source_value['asset_id']}/{$source_value['version_id']}";
                      $generator = \Drupal::service('file_url_generator');
                      return $absolute
                        ? $generator->generateAbsoluteString($uri)
                        : $generator->generateString($uri);
                    }
                  }

                  // Standard media file handling.
                  $file = $media->get($source_field)->entity;
                  if ($file instanceof FileInterface) {
                    $generator = \Drupal::service('file_url_generator');
                    return $absolute
                      ? $generator->generateAbsoluteString($file->getFileUri())
                      : $generator->generateString($file->getFileUri());
                  }
                }
              }
              $language = $this->languageManager->getCurrentLanguage()->getId();
              if ($media->hasTranslation($language)) {
                $media = $media->getTranslation($language);
              }
              return $media->toUrl()->setAbsolute($absolute)->toString();
            }
          }
          break;

        case 'view':
          $view_type = $this->entityTypeManager->getStorage('view');
          if ($view_type && $entity_data[1] && $entity_data[2]) {
            $view_id = $entity_data[1];
            $display_id = $entity_data[2];
            if ($view = $view_type->load($view_id)) {
              $executable = $view->getExecutable();
              $executable->initDisplay();
              foreach ($executable->displayHandlers as $view_display_id => $display) {
                if ($view_display_id == $display_id && $display->hasPath()) {
                  $path = $display->getPath();
                  if ($view->status() && strpos($path, '%') === FALSE) {
                    // Wrap this in a try/catch as trying to generate
                    // links to some routes may throw a
                    // NotAcceptableHttpException if they do not respond to HTML
                    // such as RESTExports.
                    try {
                      // @todo Views should expect and store a leading /. See:
                      //   https://www.drupal.org/node/2423913
                      return Url::fromUserInput('/' . $path)->setAbsolute($absolute)->toString();
                    }
                    catch (NotAcceptableHttpException $e) {
                      return '/' . $path;
                    }
                  }
                  else {
                    return '/' . $path;
                  }
                }
              }
            }
          }
          break;

        default:
          if (isset($entity_data[1])) {
            $entity_type_id = $entity_data[0];
            $entity_id = $entity_data[1];
            try {
              if (!UrlHelper::isExternal($entity_type_id) &&
                $this->entityTypeManager->hasDefinition($entity_type_id) &&
                $entity_type = $this->entityTypeManager
                  ->getStorage($entity_type_id)
              ) {
                if ($entity = $entity_type->load($entity_id)) {
                  $language = $this->languageManager->getCurrentLanguage()->getId();
                  if ($entity->hasTranslation($language)) {
                    $entity = $entity->getTranslation($language);
                  }
                  return $entity->toUrl()->setAbsolute($absolute)->toString();
                }
              }
            }
            catch (\LogicException $e) {
              // Ignore invalid entity type IDs (e.g. strings containing a
              // derivative separator) that slip past the isExternal() check.
            }

          }
          break;
      }
    }
    elseif (is_numeric($entity_data[0])) {
      // Backward compatibility ( node id )
      $nid = $entity_data[0];
      if ($entity = $this->entityTypeManager->getStorage('node')->load($nid)) {
        return $entity->toUrl()->setAbsolute($absolute)->toString();
      }
    }

    return (string) $entity_info;
  }

  /**
   * Get the payload to be sent to
   * \Drupal\cohesion\CohesionApiClient::layoutCanvasDataMerge
   *
   * @param $entity EntityJsonValuesInterface
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  public function getPayloadForLayoutCanvasDataMerge($entity) {
    $layout_canvas = $entity->getLayoutCanvasInstance();

    $component_ids = [];
    $component_content_ids = [];
    $has_components = FALSE;
    foreach ($layout_canvas->iterateCanvas() as $layout_element) {
      if ($layout_element->isComponent()) {
        $has_components = TRUE;
        $component_ids[] = $layout_element->getComponentID();
      }
      if ($layout_element->isComponentContent()) {
        $has_components = TRUE;
        $component_content_ids[] = $layout_element->getComponentContentId();
      }
    }

    $components_data = [];
    $components = Component::loadMultiple($component_ids);
    foreach ($components as $component) {
      $components_data[$component->id()] = array_merge([
        'title' => $component->get('label'),
        'category' => $component->getCategoryEntity() ? $component->getCategoryEntity()->getClass() : FALSE,
      ], $component->getDecodedJsonValues());
    }

    if (isset($this->customComponentsService)) {
      // Custom components - add to components data.
      try {
        $custom_components = $this->customComponentsService->getComponents();

        foreach ($component_ids as $component_id) {
          if (isset($custom_components[$component_id])) {
            $components_data[$component_id] = array_merge([
              'title' => $custom_components[$component_id]['name'],
              'category' => $custom_components[$component_id]['category']->get('class'),
            ], $custom_components[$component_id]['form']->getJsonValuesDecodedArray());

          }
        }
      }
      catch (\Exception $exception) {
        $this->loggerChannelFactory->get('cohesion_elements.custom_components')->error($exception->getMessage(), Error::decodeException($exception));
      }
    }

    $components_content_data = [];
    $components_content = [];

    if ($component_content_ids) {
      $components_content = $this->entityTypeManager
        ->getStorage('component_content')
        ->loadByProperties(['uuid' => $component_content_ids]);
    }

    foreach ($components_content as $component_content) {
      $category_entity = NULL;
      if($component_content->getComponent()) {
        $category_entity = $component_content->getComponent()->getCategoryEntity();
      }

      $language = $this->languageManager->getCurrentLanguage()->getId();
      if ($component_content->hasTranslation($language)) {
        $component_content = $component_content->getTranslation($language);
      }

      $components_content_data[$component_content->uuid()] = array_merge([
        'title' => $component_content->label(),
        'url' => $component_content->toUrl('edit-form')->toString(),
        'category' => $category_entity ? $category_entity->getClass() : FALSE,
      ]);
    }

    if ($has_components) {
      return [
        'layoutCanvas' => $layout_canvas->getRawDecodedJsonValues(),
        'components' => $components_data,
        'componentContent' => $components_content_data,
      ];
    }

    return FALSE;
  }

  /**
   * Should only custom styles that are used on the page be loaded.
   *
   * @return bool
   */
  public function loadCustomStylesOnPageOnly() {

    if ($this->frontEndSettings->get('css.custom_styles_on_page') == 1) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Should only custom element styles that are used on the page be loaded.
   *
   * @return bool
   */
  public function loadElementStylesOnPageOnly() {

    if ($this->frontEndSettings->get('css.element_styles_on_page') == 1) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Matches a single HTML start/end tag, capturing its name and attributes.
   *
   * Group 1: "/" if this is a closing tag. Group 2: the tag name. Group 3:
   * the raw attribute text (unparsed). Attribute values are matched quoted
   * or unquoted so the tag's closing ">" isn't mistaken for one inside a
   * quoted value (e.g. onerror="alert('a>b')").
   */
  const EXECUTABLE_HTML_TAG_PATTERN = '/<(\/?)([a-zA-Z][a-zA-Z0-9-]*)((?:[\s\/]+[^\s\/=>"\']+(?:\s*=\s*(?:"[^"]*"|\'[^\']*\'|[^\s>]+))?)*)\s*\/?>/';

  /**
   * Escapes HTML capable of executing JavaScript so it renders as text.
   *
   * Targets <script>/<iframe>/<object>/<embed>/<style>/<applet>/<base> tags,
   * any "on*" event-handler attribute, and href/src/action/formaction/poster
   * attributes using a dangerous protocol (e.g. javascript:). Matched tags
   * are escaped in place within $html rather than via DOMDocument, whose
   * HTML parser normalizes attribute quoting (e.g. unquoted values) and so
   * cannot be matched back into the original string byte-for-byte.
   * Everything outside a matched tag - including plain "&" characters - is
   * left untouched.
   *
   * @param string $html
   *   The HTML string to escape.
   *
   * @return string
   *   The HTML string with any executable elements escaped.
   */
  public function escapeExecutableHtml(string $html): string {
    if (!str_contains($html, '<')) {
      return $html;
    }

    $dangerous_tags = ['script', 'iframe', 'object', 'embed', 'style', 'applet', 'base'];
    $url_attributes = ['href', 'src', 'action', 'formaction', 'poster', 'data', 'xlink:href', 'srcset'];
    $dangerous_attribute_open_tag_balance = [];

    $result = preg_replace_callback(self::EXECUTABLE_HTML_TAG_PATTERN, function (array $match) use ($dangerous_tags, $url_attributes, &$dangerous_attribute_open_tag_balance) {
      $is_closing_tag = $match[1] === '/';
      $tag_name = strtolower($match[2]);

      $attrs_dangerous = !$is_closing_tag && $this->tagAttributesAreDangerous($match[3], $url_attributes);
      $is_self_closing = !$is_closing_tag && str_ends_with(trim($match[0]), '/>');

      if ($attrs_dangerous && !$is_self_closing) {
        $dangerous_attribute_open_tag_balance[$tag_name] = ($dangerous_attribute_open_tag_balance[$tag_name] ?? 0) + 1;
      }

      $has_unclosed_dangerous_start_tag = $is_closing_tag && (($dangerous_attribute_open_tag_balance[$tag_name] ?? 0) > 0);
      $is_dangerous = in_array($tag_name, $dangerous_tags, TRUE) || $attrs_dangerous || $has_unclosed_dangerous_start_tag;

      if ($is_dangerous && $has_unclosed_dangerous_start_tag) {
        $dangerous_attribute_open_tag_balance[$tag_name]--;
      }

      return $is_dangerous ? str_replace(['<', '>'], ['&lt;', '&gt;'], $match[0]) : $match[0];
    }, $html);

    return $result ?? $html;
  }

  /**
   * Determines whether a tag's attributes make it capable of executing JS.
   *
   * @param string $raw_attributes
   *   The tag's attribute text (between the tag name and the closing ">").
   * @param string[] $url_attributes
   *   Attribute names to check for a dangerous URI protocol.
   *
   * @return bool
   *   TRUE if an event-handler attribute, or a URL attribute using a
   *   dangerous protocol (e.g. javascript:), is present.
   */
  private function tagAttributesAreDangerous(string $raw_attributes, array $url_attributes): bool {
    preg_match_all('/([^\s\/=>"\']+)(?:\s*=\s*("[^"]*"|\'[^\']*\'|[^\s>]+))?/', $raw_attributes, $matches, PREG_SET_ORDER);

    foreach ($matches as $match) {
      $name = strtolower($match[1]);
      if (str_starts_with($name, 'on')) {
        return TRUE;
      }

      if (!in_array($name, $url_attributes, TRUE) || !isset($match[2])) {
        continue;
      }

      $value = $match[2];
      if ($value !== '' && ($value[0] === '"' || $value[0] === "'")) {
        $value = substr($value, 1, -1);
      }

      if (UrlHelper::stripDangerousProtocols($value) !== $value) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Get array of style types to process as separate Drupal libraries.
   *
   * @return string[]
   */
  public function styleTypesSeparateLibraries() {

    $processAsSeparateLibs = [];

    // If custom styles are set to only load on pages where used add
    // to $processAsSeparateLibs array, so we can process as separate libraries.
    if ($this->loadCustomStylesOnPageOnly()) {
      $processAsSeparateLibs[] = 'cohesion_custom_style';
    }

    // If custom element styles are set to only load on pages where used add
    // to $processAsSeparateLibs array, so we can process as separate libraries.
    if ($this->loadElementStylesOnPageOnly()) {
      $processAsSeparateLibs[] = 'custom_element_styles';
    }

    return $processAsSeparateLibs;
  }

}
