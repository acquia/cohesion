<?php

namespace Drupal\cohesion\Services;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

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
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CohesionUtils constructor.
   *
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(ThemeHandlerInterface $theme_handler, ThemeManagerInterface $theme_manager, EntityTypeManagerInterface $entity_type_manager) {
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
    $this->entityTypeManager = $entity_type_manager;
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
    \Drupal::logger('cohesion')->error($message);

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
   * @param $theme_info
   *
   * @return bool
   */
  private function isThemeCohesionEnabled($theme_info) {
    return property_exists($theme_info, 'info') && is_array($theme_info->info) && isset($theme_info->info['cohesion']) && $theme_info->info['cohesion'] === TRUE;
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
      if ($model->getProperty(['settings', 'schema', 'type']) === 'string' && (is_null($model->getProperty(['settings', 'schema', 'escape'])) || $model->getProperty(['settings', 'schema', 'escape']) === TRUE)) {
        $fieldValue = Html::escape($fieldValue);
      }
    }
    else {
      switch ($model->getProperty(['settings', 'type'])) {
        case 'checkboxToggle':
          if ($model->getProperty(['settings', 'toggleType']) == 'string' || $model->getProperty(['settings', 'toggleType']) == 'number') {
            if ($fieldValue && $model->getProperty(['settings', 'trueValue'])) {
              $fieldValue = $model->getProperty(['settings', 'trueValue']);
            }
            elseif (!$fieldValue && $model->getProperty(['settings', 'falseValue'])) {
              $fieldValue = $model->getProperty(['settings', 'falseValue']);
            }
            else {
              $fieldValue = '';
            }
          }
          break;

        case 'cohTextarea':
          if (is_null($model->getProperty(['settings', 'schema', 'escape'])) || $model->getProperty(['settings', 'schema', 'escape']) === TRUE) {
            $fieldValue = Html::escape($fieldValue);
          }
          break;

        case 'cohSelect':
          $fieldValue = strval($fieldValue);

          // Is the value in the endpoint based select options.
          if ($model->getProperty(['settings', 'selectType']) == 'existing') {
            // Really this should look up the value sin the endpoint, but it's not
            // possible to call the endpoint and get the valued programmatically.
            // This is some protection.
            $fieldValue = Xss::filter($fieldValue);
          }
          // Is the value in the manually predefined select options.
          else {
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
          $fieldValue = $this->pathRenderer($fieldValue);

          // check that the URL is valid - accounts for node::1, mailto:, external & internal links
          if(!UrlHelper::isValid($fieldValue)) {
            $fieldValue = '';
          }
          break;

        default:
          if (is_string($fieldValue) || is_object($fieldValue) && method_exists($fieldValue, '__toString')) {
            $content = json_decode($fieldValue);
            if ($content !== NULL && (is_object($content) || is_array($content))) {
              $fieldValue = json_encode($this->escapeJson($content));
            }
            else {
              $fieldValue = Html::escape($fieldValue);
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
  public function pathRenderer($entity_info) {
    $entity_data = explode('::', (string) $entity_info);

    if (count($entity_data) > 1) {
      switch ($entity_data[0]) {
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
                    // Wrap this in a try/catch as trying to generate links to some
                    // routes may throw a NotAcceptableHttpException if they do not
                    // respond to HTML, such as RESTExports.
                    try {
                      // @todo Views should expect and store a leading /. See:
                      //   https://www.drupal.org/node/2423913
                      return Url::fromUserInput('/' . $path)->toString();
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
            if ($entity_type = \Drupal::service('entity_type.manager')
              ->getStorage($entity_type_id)) {
              if ($entity = $entity_type->load($entity_id)) {
                $language = \Drupal::languageManager()->getCurrentLanguage()->getId();
                if ($entity->hasTranslation($language)) {
                  $entity = $entity->getTranslation($language);
                }
                return $entity->toUrl()->toString();
              }
            }

          }
          break;
      }
    }
    else {
      // Backward compatibility ( node id )
      $nid = $entity_data[0];
      if ($entity = $this->entityTypeManager->getStorage('node')->load($nid)) {
        return $entity->toUrl()->toString();
      }
    }

    return (string) $entity_info;
  }

  /**
   * Get the payload to be sent to \Drupal\cohesion\CohesionApiClient::layoutCanvasDataMerge
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
      if($layout_element->isComponent()) {
        $has_components = TRUE;
        $component_ids[] = $layout_element->getComponentID();
      }
      if($layout_element->isComponentContent()) {
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

    $components_content_data = [];
    $components_content = ComponentContent::loadMultiple($component_content_ids);
    foreach ($components_content as $component_content) {
      $category_entity = $component_content->getComponent()->getCategoryEntity();

      $components_content_data[$component_content->id()] = array_merge([
        'title' => $component_content->label(),
        'url' => $component_content->toUrl('edit-form')->toString(),
        'category' => $component_content ? $category_entity->getClass() : FALSE,
      ]);
    }

    if($has_components) {
      return [
        'layoutCanvas' => $layout_canvas->getRawDecodedJsonValues(),
        'components' => $components_data,
        'componentContent' => $components_content_data,
      ];
    }

    return FALSE;
  }

}
