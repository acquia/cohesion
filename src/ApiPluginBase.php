<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\Services\LocalFilesManager;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Config\ConfigInstallerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Class ApiPluginBase.
 *
 * Defines PluginBase for API.
 *
 * @package Drupal\cohesion
 */
abstract class ApiPluginBase extends PluginBase implements ApiPluginInterface, ContainerFactoryPluginInterface {
  use StringTranslationTrait;

  const STYLE_TYPES = [
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

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager*/
  protected $streamWrapperManager;

  /**
   * @var \Drupal\cohesion\Services\LocalFilesManager*/
  protected $localFilesManager;

  /**
   * @var \Drupal\Core\Entity\EntityInterface|null*/
  protected $entity;

  /**
   * Whether the entity being processed is a Content entity or a config entity.
   *
   * @var bool
   */
  protected $isContent;

  /**
   * The config installer.
   *
   * @var \Drupal\Core\Config\ConfigInstallerInterface
   */
  protected $configInstaller;

  /**
   * The cohesion utils helper.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * @var \Drupal\Core\Theme\ThemeManager
   */
  protected $themeManager;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  private $fileSystem;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The data to be sent to the API.
   *
   * @var object
   */
  protected $data;

  /**
   * Whether the stylesheet should return with a timestamp.
   *
   * @var bool
   */
  protected $with_timestamp = TRUE;

  /**
   * @var null
   */
  protected $response = NULL;

  /**
   * Whether to save the data in database / files (templates, stylesheet)
   *
   * @var bool
   */
  protected $saveData = TRUE;

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * ApiPluginBase constructor.
   *
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $stream_wrapper_manager
   * @param \Drupal\cohesion\Services\LocalFilesManager $local_files_manager
   * @param \Drupal\Core\Config\ConfigInstallerInterface $config_installer
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    StreamWrapperManagerInterface $stream_wrapper_manager,
    LocalFilesManager $local_files_manager,
    ConfigInstallerInterface $config_installer,
    CohesionUtils $cohesion_utils,
    ModuleHandlerInterface $module_handler,
    ThemeHandlerInterface $theme_handler,
    ThemeManagerInterface $theme_manager,
    FileSystemInterface $file_system,
    LoggerChannelFactoryInterface $loggerChannelFactory,
    MessengerInterface $messenger,
    KeyValueFactoryInterface $keyValue,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Save the injected services.
    $this->entityTypeManager = $entity_type_manager;
    $this->streamWrapperManager = $stream_wrapper_manager;
    $this->localFilesManager = $local_files_manager;
    $this->configInstaller = $config_installer;
    $this->cohesionUtils = $cohesion_utils;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
    $this->themeManager = $theme_manager;
    $this->fileSystem = $file_system;
    $this->logger = $loggerChannelFactory->get('cohesion');
    $this->messenger = $messenger;
    $this->keyValue = $keyValue;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('stream_wrapper_manager'),
      $container->get('cohesion.local_files_manager'),
      $container->get('config.installer'),
      $container->get('cohesion.utils'),
      $container->get('module_handler'),
      $container->get('theme_handler'),
      $container->get('theme.manager'),
      $container->get('file_system'),
      $container->get('logger.factory'),
      $container->get('messenger'),
      $container->get('keyvalue')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['name'];
  }

  /**
   * @param \Drupal\cohesion\Entity\EntityJsonValuesInterface $entity
   */
  public function setEntity(EntityJsonValuesInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Get the data from the API call, if no data return an empty array.
   *
   * @return array
   */
  public function getData() {
    if (isset($this->response['data'])) {
      return $this->response['data'];
    }
    else {
      return [];
    }
  }

  /**
   * Get the styles for a particular type (base, theme) for a specific theme.
   *
   * @param string $styleType
   *    - the style type (base, theme)
   * @param string $themeId
   *
   * @return string|null
   */
  public function getResponseStyles(string $styleType, string $themeId = 'current', $sgm = FALSE) {
    if ($themeId == 'current') {
      $themeId = $this->themeManager->getActiveTheme()->getName();
    }

    foreach ($this->getData() as $styles) {
      $currentStyles = [];
      if ($sgm) {
        // Get the current styles.
        $jsonStylesheet = $this->localFilesManager->getStyleSheetJson($themeId);
        $currentStyles = Json::decode($jsonStylesheet)['styles'];
      }

      // If styles have been updated/added then merge them with the
      // current styles.
      if ($stylesAdded = Json::decode($styles['css'])['styles']['added']) {
        $currentStyles = $this->array_merge_recursive_distinct($currentStyles, $stylesAdded);
      }

      foreach (self::STYLE_TYPES as $sectionName => $sectionKeys) {
        if ($sectionName === $styleType && $themeId == $styles['themeName'] && is_array($currentStyles)) {
          $styleValues = '';
          foreach ($currentStyles as $styleAdded) {
            $styleValues .= $this->processCssApiEntries($styleAdded);
          }
          return $styleValues;
        }
      }
    }
  }

  /**
   * @param $to_save
   */
  public function setSaveData($to_save) {
    $this->saveData = boolval($to_save);
  }

  /**
   * @return bool
   */
  public function getSaveData() {
    return $this->saveData;
  }

  /**
   * @inheritDoc
   *
   * @param bool $attach_css
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function prepareData($attach_css = TRUE) {
    // Set up the data object that will be sent to the style API endpoint.
    $this->data = new \stdClass();
    $this->data->settings = new \stdClass();
    // Entities to be processed.
    $this->data->settings->forms = $this->getForms();
    $this->data->settings->timestamp = $this->getWithTimestamp();
    // Global website settings needed to build the entities.
    $this->data->settings->website_settings = new \stdClass();

    // Attach the website settings data (this is needed for every request).
    $website_settings_types = [
      'base_unit_settings',
      'responsive_grid_settings',
      'default_font_settings',
    ];

    $website_settings_storage = $this->entityTypeManager->getStorage('cohesion_website_settings');

    foreach ($website_settings_types as $website_settings_type) {
      // If the one of the form being saved is a website settings use it rather
      // than the one from the database as it has the latest data.
      /** @var \Drupal\cohesion_website_settings\Entity\WebsiteSettings $website_settings */
      foreach ($this->data->settings->forms as $form) {
        if (isset($form['parent']) && is_object($form['parent']) && property_exists($form['parent'], 'type') && property_exists($form['parent'], 'bundle') &&
          $form['parent']->type == 'website_settings' && $form['parent']->bundle == $website_settings_type) {
          $this->data->settings->website_settings->$website_settings_type = $form['parent'];
        }
      }

      if (!property_exists($this->data->settings->website_settings, $website_settings_type)) {
        // Otherwise, load the entity in and use its json values.
        $website_settings = $website_settings_storage->load($website_settings_type);
        if ($website_settings) {
          $resource_object = $website_settings->getResourceObject();
          $this->data->settings->website_settings->$website_settings_type = $resource_object;
        }
      }
    }

    // Attach icon libraries (as fake website settings).
    $this->data->settings->website_settings->icon_libraries = $this->getIconGroup();
    // Attach the font stacks (as fake website settings).
    $this->data->settings->website_settings->font_libraries = $this->getFontGroup();
    $this->data->settings->website_settings->color_palette = $this->getColorGroup();
    $this->data->settings->website_settings->scss_variables = $this->getSCSSVariableGroup();

    $this->data->settings->style_guides = [];
    $this->data->css = [];
    foreach ($this->cohesionUtils->getCohesionEnabledThemes() as $theme_info) {
      if ($this->moduleHandler->moduleExists('cohesion_style_guide')) {
        // Attach the style guide manager for each theme.
        $style_guide_manager_handler = \Drupal::service('cohesion_style_guide.style_guide_handler');
        $style_guide_tokens = $style_guide_manager_handler->getTokenValues($theme_info);
        // Format the tokens for the API.
        foreach ($style_guide_tokens as &$style_guide_token) {
          $this->cohesionUtils->processTokenForApi($style_guide_token);
        }

        $base_theme = property_exists($theme_info, 'base_theme') ? $theme_info->base_theme : NULL;
        $this->data->settings->style_guides[$theme_info->getName()] = [
          'baseTheme' => $base_theme,
          'tokens' => $style_guide_tokens,
        ];
      }

      // Attach the JSON representation of the stylesheet if Site Studio is
      // enabled and is the default theme or it has been set to build assets in
      // the appearance of the theme.
      if ($this->themeHandler->getDefault() == $theme_info->getName() || theme_get_setting('features.cohesion_build_assets', $theme_info->getName())) {
        if ($attach_css == TRUE) {
          $this->data->css[$theme_info->getName()] = $this->localFilesManager->getStyleSheetJson($theme_info->getName());
        }
        else {
          $this->data->css[$theme_info->getName()] = '';
        }
      }
    }

    // Attach a generic theme if the templates should be built but not the
    // styles (main use is for AMP page).
    if (!empty($this->cohesionUtils->getCohesionTemplateOnlyEnabledThemes())) {
      $this->data->css['coh-generic-theme'] = '';
    }
  }

  /**
   * Get combined icon library for settings and forms.
   *
   * @return array|string
   */
  public function getIconGroup() {
    $return = [
      'title' => 'Icon libraries',
      'type' => 'website_settings',
      'bundle' => 'icon_libraries',
    ];

    $icon_library_values = [];

    try {
      /** @var \Drupal\cohesion_website_settings\Entity\IconLibrary $icon_library_entity */
      foreach ($this->entityTypeManager->getStorage('cohesion_icon_library')
        ->loadMultiple() as $icon_library_entity) {
        $icon_library_values['iconLibraries'][] = ['library' => $this->patchUri($icon_library_entity->getDecodedJsonValues())];
      }
      $return['values'] = $icon_library_values;
    }
    catch (\Exception $e) {
      $return = [];
    }

    return $return;
  }

  /**
   * Get combined font stacks for settings and forms.
   *
   * @return array|string
   */
  public function getFontGroup() {
    $return = [
      'title' => 'Font stacks',
      'type' => 'website_settings',
      'bundle' => 'font_libraries',
    ];

    $font_stack_values = [];
    $font_library_values = [];

    try {
      /** @var \Drupal\cohesion_website_settings\Entity\FontStack $font_stack_entity */
      foreach ($this->entityTypeManager->getStorage('cohesion_font_stack')
        ->loadMultiple() as $font_stack_entity) {
        $font_stack_values[] = ['stack' => $this->patchUri($font_stack_entity->getDecodedJsonValues())];
      }

      $return['values'] = ['fontStacks' => $font_stack_values];

      /** @var \Drupal\cohesion_website_settings\Entity\FontLibrary $font_library_entity */
      foreach ($this->entityTypeManager->getStorage('cohesion_font_library')
        ->loadMultiple() as $font_library_entity) {
        $font_library_values[] = ['library' => $this->patchUri($font_library_entity->getDecodedJsonValues())];
      }

      $return['values']['uploadFonts'] = $font_library_values;
    }
    catch (\Exception $e) {
      $return = [];
    }

    return $return;
  }

  /**
   * Get combined font stacks for settings and forms.
   *
   * @return array|string
   */
  public function getColorGroup() {
    $return = [
      'title' => 'Color palette',
      'type' => 'website_settings',
      'bundle' => 'color_palette',
    ];

    $color_values = [];

    try {
      /** @var \Drupal\cohesion_website_settings\Entity\Color $color_entity */
      foreach ($this->entityTypeManager->getStorage('cohesion_color')
        ->loadMultiple() as $color_entity) {
        $color_values[] = $color_entity->getDecodedJsonValues();
      }
      $return['values'] = ['colors' => $color_values];
    }
    catch (\Exception $e) {
      $return = [];
    }

    return $return;
  }

  /**
   * Get combined scss variables for settings and forms.
   *
   * @return array|string
   */
  public function getSCSSVariableGroup() {
    $return = [
      'title' => 'SCSS variable',
      'type' => 'website_settings',
      'bundle' => 'scss_variable',
    ];

    $scss_variable_values = [];

    try {
      /** @var \Drupal\cohesion_website_settings\Entity\SCSSVariable $scss_variable_entity */
      foreach ($this->entityTypeManager->getStorage('cohesion_scss_variable')
        ->loadMultiple() as $scss_variable_entity) {
        $scss_variable_values[] = $scss_variable_entity->getDecodedJsonValues();
      }
      $return['values'] = ['variables' => $scss_variable_values];

    }
    catch (\Exception $e) {
      $return = [];
    }

    return $return;
  }

  /**
   * Replace URI to relative path for the API to process.
   *
   * @param $object
   *
   * @return mixed
   */
  private function patchUri($object) {
    foreach ($object as &$value) {
      if (!empty($value)) {
        if (is_array($value) || is_object($value)) {
          $value = $this->patchUri($value);
        }
        elseif (strpos($value, '://') !== FALSE) {
          if ($local_stream_wrappers = $this->streamWrapperManager->getWrappers(StreamWrapperInterface::ALL)) {
            foreach ($local_stream_wrappers as $scheme => $scheme_value) {
              $uri = $scheme . '://';
              $new_value = str_replace('"', '', $value);
              if (strpos($new_value, $uri) === 0) {
                $stream_wrapper = $this->streamWrapperManager->getViaScheme($scheme);
                $stream_wrapper->setUri($new_value);
                $base_path = \Drupal::request()->getSchemeAndHttpHost();
                $value = str_replace($base_path, '', $stream_wrapper->getExternalUrl());
              }
            }
          }
        }
      }
    }

    return $object;
  }

  /**
   * Extract the stylesheets from the $this->response and apply them (check
   * timestamps, etc).
   *
   * @param $requestCSSTimestamp
   *
   * @return bool
   */
  protected function processStyles($requestCSSTimestamp) {
    $runningDx8Batch = &drupal_static('running_dx8_batch');
    $currentCssTimestamp = $this->localFilesManager->getStylesheetTimestamp();

    // Check if the stylesheets have updated since the request was made.
    if ($currentCssTimestamp != $requestCSSTimestamp) {
      $this->messenger->addError($this->t('The main stylesheet has been updated by another user since you saved. Please try again.'));
      return FALSE;
    }

    // Do not process styles for theme that should only generate templates.
    $templateOnlyThemes = $this->cohesionUtils->getCohesionTemplateOnlyEnabledThemes();
    foreach ($this->getData() as $styles) {
      if (isset($styles['css']) && $styles['themeName'] && !in_array($styles['themeName'], $templateOnlyThemes)) {

        // Current theme ID that we're processing.
        $themeId = $styles['themeName'];

        // Create directory if not exist.
        if (!is_dir(COHESION_CSS_PATH) && !file_exists(COHESION_CSS_PATH)) {
          $this->fileSystem->mkdir(COHESION_CSS_PATH, 0777, FALSE);
        }

        $jsonStylesheet = $this->localFilesManager->getStyleSheetJson($themeId);
        $currentJsonStyles = JSON::decode($jsonStylesheet);

        // Decode the css json in order to save it as css files
        if ($cssDiffs = Json::decode($styles['css'])) {
          $filterDiffs = array_filter(array_map('array_filter', $cssDiffs));

          if (!empty($filterDiffs)) {
            $updatedStyles = $currentJsonStyles;
            foreach ($cssDiffs as $cssDiffKey => $cssDiff) {
              $currentStyles = [];
              if (isset($currentJsonStyles[$cssDiffKey])) {
                $currentStyles = $currentJsonStyles[$cssDiffKey];
              }
              $updatedStyles[$cssDiffKey] = $this->processStyleDiff($currentStyles, $cssDiff);

              if ($cssDiffKey == 'styles') {
                foreach (self::STYLE_TYPES as $sectionName => $sectionKeys) {
                  $cssValues = array_intersect_key($updatedStyles[$cssDiffKey], array_flip($sectionKeys));
                  $this->processSectionCss($sectionName, $cssValues, $themeId, $runningDx8Batch);
                }
              } elseif ($cssDiffKey == 'prefixed') {
                $cssValues = $updatedStyles[$cssDiffKey];
                $this->processSectionCss('prefixed', $cssValues, $themeId, $runningDx8Batch);
              }

              if (!$runningDx8Batch) {
                $this->localFilesManager->refreshCaches();
              }
            }

            // Get the new json values and save to stylesheet json.
            $newStyleJson = json_encode($updatedStyles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_FORCE_OBJECT);

            try {
              $this->localFilesManager->setStyleSheetJson($newStyleJson, $themeId);
            } catch (\Throwable $e) {
              $this->cohesionUtils->errorHandler('The specified file: ' . $this->localFilesManager->getStyleSheetFilename('json', $themeId) . ' could not be saved for "' . $this->entity->getEntityTypeId() . '" entity "' . $this->entity->label() . '"');
            }
          }

          // Get the success message from the class definition.
          if ($this->entity) {
            $this->messenger->addMessage(get_class($this->entity)::STYLES_UPDATED_SAVE_MESSAGE);
          }
        }
      }
    }

    // Generate cache busting token for wysiwyg cohesion styles.
    $this->keyValue->get('cohesion.wysiwyg_cache_token')->set('cache_token', uniqid());

    return TRUE;
  }

  /**
   * Process the styles diff from the API.
   *
   * @param $currentStyles
   * @param $cssDiff
   * @return array
   */
  protected function processStyleDiff($currentStyles, $cssDiff) {
    // Merge both added and updated styles
    $stylesAdded = $cssDiff['added'] ?? [];
    $stylesUpdated = $cssDiff['updated'] ?? [];

    // Fix for custom style re-ordering.
    if (isset($currentStyles['cohesion_custom_style'])) {
      $customStylesMerge = array_merge_recursive($stylesUpdated['cohesion_custom_style'] ?? [], $stylesAdded['cohesion_custom_style'] ?? []) ?? [];
      $currentStyles['cohesion_custom_style'] = array_merge($customStylesMerge, $currentStyles['cohesion_custom_style']);
    }

    $updatedStyles = $this->array_merge_recursive_distinct($currentStyles, $stylesAdded);
    $updatedStyles = $this->array_merge_recursive_distinct($updatedStyles, $stylesUpdated);

    foreach ($stylesAdded as $styleTypeKey => $styleAdded) {
      foreach ($styleAdded as $styleKey => $style) {
        if (isset($cssDiff['added'][$styleTypeKey][$styleKey])) {
          $updatedStyles[$styleTypeKey][$styleKey] = $cssDiff['added'][$styleTypeKey][$styleKey];
        }
      }
    }

    // If we're deleting a style.
    if (!empty($cssDiff['deleted'])) {
      foreach ($updatedStyles as $k => $updatedStyle) {
        if (isset($cssDiff['deleted']['styles'][$k])) {
          foreach ($cssDiff['deleted']['styles'][$k] as $key => $deletedStyle) {
            unset($updatedStyles[$k][$key]);
          }
        }
      }
    }

    return $updatedStyles;
  }

  /**
   * Check the styles directory for the "section" exists & return the path.
   *
   * @param $sectionName
   * @return string
   */
  private function checkStylesDirectory($sectionName) {
    $basePath = COHESION_CSS_PATH . '/' . $sectionName;
    if (!is_dir($basePath) && !file_exists($basePath)) {
      $this->fileSystem->mkdir($basePath, 0777, FALSE);
    }

    return $basePath;
  }

  /**
   * @param $sectionName
   * @param $cssValues
   * @param $themeId
   * @param $runningDx8Batch
   * @return void
   */
  private function processSectionCss($sectionName, $cssValues, $themeId, $runningDx8Batch) {
    // Make sure the directory exists & return it.
    $basePath = $this->checkStylesDirectory($sectionName);
    $customStylesLoad = $this->cohesionUtils->loadCustomStylesOnPageOnly();
    $processAsSeparateLibs = $this->cohesionUtils->styleTypesSeparateLibraries();

    $sectionCss = '';
    foreach ($cssValues as $key => $css) {
      // If custom style then create a stylesheet to attach to
      // SGM, component & helper previews.
      if ($customStylesLoad && $key === 'cohesion_custom_style') {
        $this->checkStylesDirectory('preview');
        $customStyleFullPath = $this->localFilesManager->getStyleSheetFilename('preview');
        $this->saveCssEntry($this->processCssApiEntries($css), $customStyleFullPath);
      }

      // handle style types as individual CSS files & libraries.
      if (in_array($key, $processAsSeparateLibs) && $sectionName !== 'prefixed') {
        foreach ($css as $elementKey => $elementCss) {
          if ($elementCss) {
            $cssFilename = $key . '-' . $elementKey . '.css';
            $fullPathDestination = $basePath . '/' . str_replace('_', '-', $cssFilename);
            $this->saveCssEntry($elementCss, $fullPathDestination);
          }
        }
      } else {
        $sectionCss .= $this->processCssApiEntries($css);
      }
    }

    try {
      $destination = $this->localFilesManager->getStyleSheetFilename($sectionName, $themeId);
      $this->saveCssEntry($sectionCss, $destination);

      if (!$runningDx8Batch) {
        $this->logger->notice($this->t(':name stylesheet has been updated', [':name' => $sectionName]));
      }
    } catch (\Throwable $e) {
      $this->messenger->addError(t('The file could not be created.'));
    }
  }

  /**
   * Process the CSS values.
   *
   * @param $cssValues
   * @return string
   */
  public function processCssApiEntries($cssValues) {

    $styleValues = '';
    foreach ($cssValues as $css) {
      $cssData = '';
      if (is_array($css)) {
        $cssData = implode("\n", array_filter($css)) . "\n";
      } elseif (is_string($css)) {
        $cssData = $css . "\n";
      }

      $styleValues .= $cssData;
    }

    return $styleValues;
  }

  /**
   * Save the CSS entries.
   *
   * @param $css
   * @param $destination
   * @return void
   */
  private function saveCssEntry($css, $destination) {

    if (is_array($css)) {
      $css = implode("\n", array_filter($css)) . "\n";
    }

    $css = "\n" . str_replace([
      "\r\n",
      "\n\n",
    ], "\n", ltrim($css)) . "\n";

    $cssData = \Drupal::service('twig')
      ->renderInline($css)
      ->__toString();

    try {
      $this->fileSystem->saveData($cssData, $destination, FileSystemInterface::EXISTS_REPLACE);
    }
    catch (\Throwable $e) {
      $this->messenger->addError(t('The file could not be created.'));
    }
  }

  /**
   * Custom function similar to PHP array_merge_recursive().
   *
   * @param array $array1
   * @param array $array2
   * @return array
   */
  private function array_merge_recursive_distinct(array &$array1, array &$array2) {
    $merged = $array1;

    foreach ($array2 as $key => &$value) {
      if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
        $merged[$key] = $this->array_merge_recursive_distinct($merged[$key], $value);
      } else {
        $merged[$key] = $value;
      }
    }

    return $merged;
  }

  /**
   * Method performing the call to the cohesion API.
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function send() {
    // If in update.php mode, don't send.
    $dx8_no_send_to_api = &drupal_static('dx8_no_send_to_api');
    // Process entity if Site studio is enable && if is an entity, it is enable
    // and we don't want to save the data.
    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');

    if ($dx8_no_send_to_api || !($this->cohesionUtils->usedx8Status()) ||
      ((isset($this->entity) && method_exists($this->entity, 'status') && !$this->entity->status()) && !$this->getSaveData()) ||
      ($cohesion_sync_lock) || $this->configInstaller->isSyncing()) {
      return TRUE;
    }

    $this->isContent = $this->entity instanceof CohesionLayout;

    // Don't attach the css to inject for content requests.
    $this->prepareData(!$this->isContent);

    // Whether the generated template should have its content translatable in
    // interface translation.
    $this->data->translatable = !$this->isContent;

    $this->data->settings->forms = array_values($this->data->settings->forms);

    // Allow modules to manipulate the data before it's sent.
    $this->moduleHandler->alter('dx8_api_outbound_data', $this->data, $this->entity, $this->isContent);

    // Save the last time the main stylesheet was updated.
    $requestCSSTimestamp = $this->localFilesManager->getStylesheetTimestamp();

    // Perform the send (this function exists on the child classes).
    $this->callApi();

    // Process the response.
    if ($this->response && floor($this->response['code'] / 200) == 1) {

      // If this a layout_field, just return the entire request (as it will be
      // store inline in the field).
      if ($this->isContent || !$this->getSaveData()) {
        return TRUE;
      }

      // Attempt to process the stylesheets received back from the API (merge
      // into the existing stylesheet).
      if ($this->processStyles($requestCSSTimestamp)) {
        return TRUE;
      }
      // Timestamp to the CSS is now later than when the request was made.
      else {
        return FALSE;
      }
    }
    else {
      if (isset($this->entity)) {
        if (($this->entity instanceof CohesionLayout) && $this->entity->getParentEntity()) {
          /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $entity */
          $label = $this->entity->getParentEntity()->label();
          $entity_type = $this->entity->getParentEntity()
            ->getEntityType()
            ->getLabel();
        }
        else {
          /** @var \Drupal\Core\Entity\Entity $entity */
          $label = $this->entity->label();
          $entity_type = $this->entity->getEntityType()->getLabel();
        }
        \Drupal::service('cohesion.utils')->errorHandler(
          $this->t('API Error while trying to save @entity_type - @label', [
            '@entity_type' => $entity_type,
            '@label' => $label,
          ])
        );
      }
    }

    return FALSE;
  }

  /**
   * Send data to be compiled to the API without saving any assets.
   */
  public function sendWithoutSave() {
    $this->setSaveData(FALSE);
    return $this->send();
  }

  /**
   * Set build the stylesheets without timestamp prepended.
   */
  public function setWithTimestamp($with_timestamp = TRUE) {
    $this->with_timestamp = $with_timestamp;
  }

  /**
   * Get build the stylesheets without timestamp prepended.
   */
  public function getWithTimestamp() {
    return $this->with_timestamp;
  }

  /**
   * Delete a style/template styles using the API and apply the modified
   * stylesheet.
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function delete() {
    // Prevent sending data to API if Use DX8 has error.
    if (!($this->cohesionUtils->usedx8Status()) || $this->configInstaller->isSyncing()) {
      return FALSE;
    }

    $this->prepareData();

    if (strstr($this->entity->getEntityTypeId(), '_template')) {
      $this->data->delete_id = $this->entity->getEntityTypeId() . '_' . $this->entity->id();
    }

    // Components use id format of `cohesion_component_entity_id`.
    if ($this->entity->getEntityTypeId() === 'cohesion_component') {
      $this->data->delete_id = $this->entity->getEntityTypeId() . '_' . $this->entity->id();
    }

    // Default delete_id.
    if (isset($this->data->delete_id) === FALSE) {
      $this->data->delete_id = $this->entity->id() . '_' . $this->entity->getConfigItemId();
    }

    // Save the last time the main stylesheet was updated.
    $requestCSSTimestamp = $this->localFilesManager->getStylesheetTimestamp();

    // Call the API to delete the entry from the stylesheet.
    $this->response = \Drupal::service('cohesion.api_client')->buildDeleteStyle($this->data);

    if ($this->response && floor($this->response['code'] / 200) == 1) {

      // Attempt to process the stylesheets received back from the API (merge
      // into the exist stylesheet).
      if ($this->processStyles($requestCSSTimestamp)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Process icon library and responsive grid settings stylesheets for admin.
   *
   * @param $stylesDiff
   * @param $key
   * @param $StyleSheetFilename
   * @param $name
   * @return void
   */
  protected function processWebsiteSettingsDiff($stylesDiff, $key, $StyleSheetFilename, $name) {
    $runningDx8Batch = &drupal_static('running_dx8_batch');
    $destination = $this->localFilesManager->getStyleSheetFilename($StyleSheetFilename);

    $added = ($stylesDiff['added']['cohesion_website_settings'][$key] ?? '');
    $updated = ($stylesDiff['updated']['cohesion_website_settings'][$key] ?? '');
    $updateStyles = $added . $updated;

    try {
      $this->fileSystem->saveData($updateStyles, $destination, FileSystemInterface::EXISTS_REPLACE);

      if (!$runningDx8Batch) {
        $this->logger->notice(t(':name stylesheet has been updated', [':name' => $name]));
      }
    }
    catch (\Throwable $e) {
      $this->messenger->addError(t('The file could not be created.'));
    }
  }

}
