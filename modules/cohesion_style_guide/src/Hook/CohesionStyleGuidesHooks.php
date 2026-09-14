<?php

namespace Drupal\cohesion_style_guide\Hook;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\Services\RebuildInuseBatch;
use Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Hook implementations for Cohesion style guide.
 */
class CohesionStyleGuidesHooks {

  public function __construct(
    protected ConfigFactoryInterface $config_factory,
    protected EntityTypeManagerInterface $entity_type_manager,
    protected CohesionUtils $cohesion_utils,
    protected StyleGuideManagerHandler $style_guide_manager_handler,
    protected MessengerInterface $messenger,
    protected ExtensionPathResolver $extension_path_resolver,
    protected RebuildInuseBatch $rebuild_inuse_batch,
    protected ThemeManagerInterface $theme_manager,
  ) {}

  /**
   * Implements hook_form_system_theme_settings_alter().
   */
  #[Hook('form_system_theme_settings_alter')]
  public function form_system_theme_settings_alter(&$form, FormStateInterface $form_state) {
    // Get the theme id from the theme settings being edited.
    $build_info = $form_state->getBuildInfo();
    $args = $build_info['args'];

    if (isset($args[0])) {

      $theme_id = $args[0];

      // Check if the theme settings being edited are part of a theme with
      // cohesion enabled.
      if ($this->cohesion_utils->themeHasCohesionEnabled($theme_id)) {
        if ($this->config_factory->get('system.performance')->get('css.preprocess') === TRUE) {
          $this->messenger->addWarning('Please note, Style Guide preview is disabled as CSS aggregation is turned on.');
          $form['#attached']['library'][] = 'cohesion_style_guide/hide-preview';
        }

        $form['#attached']['drupalSettings']['cohesion']['style_guides'] = $this->style_guide_manager_handler->getJsonDefinition();
        $style_guide_manager_json_values = $this->style_guide_manager_handler->getStyleGuideManagerJsonWithParentMerged($theme_id);
        $form['#attached']['drupalSettings']['cohesion']['parentEntityForm']['json_values'] = json_decode($style_guide_manager_json_values['parent']);
        $form['#attached']['drupalSettings']['cohesion']['fieldPreview'] = $this->style_guide_manager_handler->tokensCanBePreview();

        $module_path = $this->extension_path_resolver->getPath('module', 'cohesion_elements');
        $form['#attached']['drupalSettings']['cohesion']['canvas_preview_css'] = $module_path . '/css/canvas-preview.css';
        $form['#attached']['drupalSettings']['cohesion']['canvas_preview_js'] = $module_path . '/js/canvas-preview.js';

        $form['cohesion'] = [
          // Drupal\cohesion\Element\CohesionField.
          '#type' => 'cohesionfield',
          '#json_values' => $style_guide_manager_json_values['theme'],
          '#classes' => [],
          '#entity' => NULL,
          '#cohFormGroup' => 'style_guide',
          '#cohFormId' => 'style_guide_manager',
          '#weight' => 0,
          '#isContentEntity' => FALSE,
        ];

        // Add the shared attachments.
        _cohesion_shared_page_attachments($form);

        array_unshift($form['#submit'], 'cohesion_style_guide_theme_settings_pre_submit');
        $form['#submit'][] = 'cohesion_style_guide_theme_settings_submit';
      }
    }
  }

  /**
   * Implements hook_token_info().
   */
  #[Hook('token_info')]
  public function token_info() {
    // Defines style guide manager tokens.
    $info = [];
    $info['types']['style-guide'] = [
      'name' => t('Style guide'),
      'description' => t('Tokens for Site Studio style guide'),
    ];

    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuide[] $style_guides */
    $style_guide_storage = $this->entity_type_manager
      ->getStorage('cohesion_style_guide');
    $style_guide_ids = $style_guide_storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight')
      ->condition('status', TRUE)
      ->execute();
    $style_guides = $style_guide_storage->loadMultiple($style_guide_ids);

    foreach ($style_guides as $style_guide) {
      $layout_canvas = $style_guide->getLayoutCanvasInstance();

      $info['types'][$style_guide->id()] = [
        'name' => $style_guide->label(),
        'description' => t('Site Studio style guide tokens for %s', ['%s' => $style_guide->label()]),
        'nested' => TRUE,
      ];

      $info['tokens']['style-guide'][$style_guide->id()] = [
        'name' => $style_guide->label(),
        'description' => t('Site Studio style guide tokens for %s', ['%s' => $style_guide->label()]),
        'type' => $style_guide->id(),
      ];

      foreach ($layout_canvas->iterateStyleGuideForm() as $form_element) {

        $token = $form_element->getModel()->getProperty([
          'settings',
          'machineName',
        ]);
        $token_name = $form_element->getModel()->getProperty([
          'settings',
          'title',
        ]);

        if ($token && $token_name) {
          $info['tokens'][$style_guide->id()][$token] = ['name' => $token_name];
        }

      }
    }

    return $info;
  }

  /**
   * Implements hook_tokens().
   */
  #[Hook('tokens')]
  public function tokens($type, $tokens) {
    $replacements = [];
    if ($type == 'style-guide') {
      $theme = $this->theme_manager->getActiveTheme()->getExtension();
      $style_guide_manager_token_values = $this->style_guide_manager_handler->getTokenValues($theme);
      foreach ($tokens as $name => $original) {
        if (isset($style_guide_manager_token_values[$name])) {
          if (is_array($style_guide_manager_token_values[$name])) {
            $replacements[$original] = json_encode($style_guide_manager_token_values[$name]);
          }
          else {
            $replacements[$original] = $style_guide_manager_token_values[$name];
          }
        }
      }
    }
    return $replacements;
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme() {
    return [
      'toolbar_tray' => [
        'variables' => ['entities' => []],
      ],
    ];
  }

}
