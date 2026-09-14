<?php

namespace Drupal\cohesion_templates\Hook;

use Drupal\block_content\BlockContentInterface;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\views\ViewExecutable;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 *
 */
class CohesionTemplatesHooks {

  public function __construct(
    protected EntityTypeManagerInterface $entity_type_manager,
    protected KeyValueFactoryInterface $key_value_store,
    protected ThemeManagerInterface $theme_manager,
    protected ConfigFactoryInterface $config_factory,
    protected CohesionUtils $cohesion_utils,
    protected RequestStack $request_stack,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name, RouteMatchInterface $route_match) {
    switch ($route_name) {
      // Main module help for the cohesion_templates module.
      case 'help.page.cohesion_templates':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('This module defines the template configuration entities for creating Site Studio templates.') . '</p>';
        $output .= '<p><ul>';
        $output .= '  <li>Master/global template configuration entity and supporting forms.</li>';
        $output .= '  <li>Content template view mode configuration entity and supporting forms.</li>';
        $output .= '  <li>Menu template configuration entity and supporting forms.</li>';
        $output .= '  <li>Views template configuration entity and supporting forms.</li>';
        $output .= '  <li>Content entity template selector field plugin.</li>';
        $output .= '  <li>Twig extension containing twig functions and filters required for rendering Site Studio templates.</li>';
        $output .= '  <li>Site Studio API integration.</li>';
        $output .= '</ul></p>';
        $output .= '<p><a href="https://docs.acquia.com/" target="_blank">https://docs.acquia.com/</a></p>';
        return $output;

      default:
    }
  }

  /**
   * @inheritdoc
   *
   * Add cohesion template cache tags if template is used on this entity.
   */
  #[Hook('entity_view')]
  public function entity_view(array &$build, EntityInterface $entity, EntityViewDisplayInterface $display, $view_mode): void {
    if ($entity instanceof ContentEntityInterface) {
      $candidates = _cohesion_templates_get_template_candidate($entity, $view_mode);
      $candidate_template_ids = $candidates['candidate_template_ids'];

      // Cache tag for the chosen template.
      $build['#cache']['tags'][] = 'cohesion.templates.' . $entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode . '.' . $candidates['chosen_template'];
      // Cache tag for the view mode.
      $build['#cache']['tags'][] = 'cohesion.templates.' . $entity->getEntityTypeId() . '.' . $view_mode;

      if (count($candidate_template_ids) > 0) {
        $candidate_template_id = reset($candidate_template_ids);

        if (!isset($build['#cache']['contexts'])) {
          $build['#cache']['contexts'] = [];
        }
        $candidate_template_storage = $this->entity_type_manager
          ->getStorage('cohesion_content_templates');

        $context_cache_metadata = \Drupal::service('cohesion_templates.context.cache_metadata');
        $candidate_template = $candidate_template_storage->load($candidate_template_id);
        $context_names = $context_cache_metadata->extractContextNames($candidate_template);

        // Fetch cache metadata from Context.
        if (!empty($context_names)) {
          $cache = $context_cache_metadata->getContextsCacheMetadata($context_names);
        }
        else {
          $cache = [
            'tags' => [],
            'contexts' => [],
          ];
        }

        // Fetch catch metadata from template.
        $cache['tags'] = array_merge($cache['tags'], $candidate_template->getCacheTags());
        $cache['contexts'] = array_merge($cache['contexts'], $candidate_template->getCacheContexts());

        // Merge cache contexts.
        if (isset($build['#cache']['contexts'])) {
          $build['#cache']['contexts'] = array_merge($build['#cache']['contexts'], $cache['contexts']);
        }
        else {
          $build['#cache']['contexts'] = $cache['contexts'];
        }

        // Merge cache tags.
        if (isset($build['#cache']['tags'])) {
          $build['#cache']['tags'] = array_merge($build['#cache']['tags'], $cache['tags']);
        }
        else {
          $build['#cache']['tags'] = $cache['tags'];
        }
      }
    }
  }

  /**
   * Implements hook_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocess_page(&$variables): void {
    $master_template = _cohesion_templates_get_master_template();
    if ($master_template) {
      if (!isset($variables['page']['#cache']['tags'])) {
        $variables['page']['#cache']['tags'] = [];
      }
      $variables['page']['#cache']['tags'][] = 'cohesion.templates.' . $master_template;

      $candidate_template_storage = $this->entity_type_manager
        ->getStorage('cohesion_master_templates');
      $candidate_template = $candidate_template_storage->load($master_template);
      $context_cache_metadata = \Drupal::service('cohesion_templates.context.cache_metadata');
      $context_names = $context_cache_metadata->extractContextNames($candidate_template);

      // Fetch cache metadata from Context.
      if (!empty($context_names)) {
        $cache = $context_cache_metadata->getContextsCacheMetadata($context_names);
      }
      else {
        $cache = [
          'tags' => [],
          'contexts' => [],
        ];
      }

      // Fetch catch metadata from template.
      $cache['tags'] = array_merge($cache['tags'], $candidate_template->getCacheTags());
      $cache['contexts'] = array_merge($cache['contexts'], $candidate_template->getCacheContexts());

      // Merge cache contexts.
      if (isset($variables['page']['#cache']['contexts'])) {
        $variables['page']['#cache']['contexts'] = array_merge($variables['page']['#cache']['contexts'], $cache['contexts']);
      }
      else {
        $variables['page']['#cache']['contexts'] = $cache['contexts'];
      }

      // Merge cache tags.
      if (isset($variables['page']['#cache']['tags'])) {
        $variables['page']['#cache']['tags'] = array_merge($variables['page']['#cache']['tags'], $cache['tags']);
      }
      else {
        $variables['page']['#cache']['tags'] = $cache['tags'];
      }
    }
  }

  /**
   * Add Site Studio template cache tags to views that use them.
   *
   * @param \Drupal\views\ViewExecutable $view
   * @param $display_id
   * @param array $args
   */
  #[Hook('views_pre_view')]
  public function views_pre_view(ViewExecutable $view, $display_id, array &$args): void {

    try {
      $view_entity = $view->storage;
      $displays = $view_entity->get('display');

      if (is_array($displays)) {

        // Merge in the default.
        if (isset($displays['default']['display_options']['style']['options']) && !isset($displays[$display_id]['display_options']['style']['options'])) {
          $displays[$display_id]['display_options'] = array_merge($displays['default']['display_options'], $displays[$display_id]['display_options']);
        }

        // Get the Site Studio views template ID.
        if (isset($displays[$display_id]['display_options']['style']['options']['views_template'])) {
          $views_template_id = $displays[$view->current_display]['display_options']['style']['options']['views_template'];

          // Add the cache tag for this template.
          $view->element['#cache']['tags'][] = 'cohesion.templates.' . $views_template_id;
        }
      }
    }
    catch (\Exception $e) {
    }

  }

  /**
   * @inheritdoc
   * Suggest correct Site Studio entity template as appropriate.
   *
   * There are several different types of Content Templates, which have specific
   * requirements for their usage.
   *
   * List of content template types from most- to least-specific.
   * - Type-specific full content (i.e. Article, full content)
   * - View-mode and type-specific content (i.e. Article, teaser view)
   * - View-mode fall-back (i.e. All content, teaser view)
   *
   * All content templates are enabled in the content type edit screen.
   */
  public function suggestions(array &$suggestions, ContentEntityInterface $entity, $view_mode, $hook): void {
    if ($view_mode == 'default') {
      $view_mode = 'full';
    }

    $candidates = _cohesion_templates_get_template_candidate($entity, $view_mode);
    $candidate_template_ids = $candidates['candidate_template_ids'];
    $chosen_template = $candidates['chosen_template'];

    // Load suitable templates, if any are available.
    $storage = $this->key_value_store->get('coh_master_templates');
    if (count($candidate_template_ids) > 0) {
      $candidate_template_id = reset($candidate_template_ids);
      $candidate_template_storage = $this->entity_type_manager
        ->getStorage('cohesion_content_templates');
      $candidate_template = $candidate_template_storage->load($candidate_template_id);

      // Add suggestions.
      $suggestions[] = $hook . '__cohesion__' . $candidate_template->get('id');
      $suggestions[] = $hook . '__cohesion__' . $candidate_template->get('id') . '__' . $this->theme_manager->getActiveTheme()->getName();

      if ($view_mode == 'full' && $candidate_template->get('master_template') != '__none__') {
        $master_template_storage = $this->entity_type_manager
          ->getStorage('cohesion_master_templates');
        $master_template = $master_template_storage->load($candidate_template->get('master_template'));

        if ($master_template && $master_template->status()) {
          $storage->set($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode . '.' . $chosen_template, $candidate_template->get('master_template'));
          return;
        }
      }
    }

    $storage->set($entity->getEntityTypeId() . '.' . $entity->bundle() . '.' . $view_mode . '.' . $chosen_template, NULL);

  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    return [
      'cohesion_templates' => [
        'template' => 'cohesion_templates',
        'render element' => 'children',
      ],
    ];
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   *
   * Suggest correct Site Studio page template as appropriate.
   *
   * A page template is used when the active node template specifies one.
   */
  #[Hook('theme_suggestions_page_alter')]
  public function theme_suggestions_page_alter(array &$suggestions, array $variables): void {
    // Only apply master templates if this theme is Site Studio enabled.
    if ($this->cohesion_utils->currentThemeUseCohesion()) {
      $master_template_candidate = _cohesion_templates_get_master_template();
      $master_template = 'page__cohesion__' . $master_template_candidate;
      if ($master_template_candidate !== FALSE && ($this->request_stack->getCurrentRequest()->query->get('coh_clean_page') !== 'true')) {
        $suggestions[] = $master_template;
        $suggestions[] = $master_template . '__' . $this->theme_manager->getActiveTheme()->getName();
      }
    }
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_node_alter')]
  public function theme_suggestions_node_alter(array &$suggestions, array &$variables): void {
    $this->suggestions($suggestions, $variables['elements']['#node'], $variables['elements']['#view_mode'], 'node');
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_taxonomy_term_alter')]
  public function theme_suggestions_taxonomy_term_alter(array &$suggestions, array &$variables): void {
    $this->suggestions($suggestions, $variables['elements']['#taxonomy_term'], $variables['elements']['#view_mode'], 'taxonomy_term');
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_media_alter')]
  public function theme_suggestions_media_alter(array &$suggestions, array &$variables): void {
    $this->suggestions($suggestions, $variables['elements']['#media'], $variables['elements']['#view_mode'], 'media');
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_views_view_alter')]
  public function theme_suggestions_views_view_alter(array &$suggestions, array &$variables): void {

    $current_theme = $this->theme_manager->getActiveTheme();
    $is_admin = $this->config_factory->get('system.theme')
      ->get('admin') == $current_theme->getName();

    if (!$is_admin) {
      // Get the view/display as a string.
      $route_match = '';

      try {
        $route_match .= $variables['view']->storage->get('id');
        $route_match .= $variables['view']->current_display;
      }
      catch (\Exception $e) {
        return;
      }

      // Get the master template and add to the key/val.
      try {
        $storage = $this->key_value_store->get('coh_master_templates_route_match');

        if (array_key_exists('master_template', $variables['view']->style_plugin->options) && ($master_template = $variables['view']->style_plugin->options['master_template'])) {
          $storage->set($route_match, $master_template);
        }
      }
      catch (\Exception $e) {

      }
    }
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_user_alter')]
  public function theme_suggestions_user_alter(array &$suggestions, array &$variables): void {
    $this->suggestions($suggestions, $variables['elements']['#user'], $variables['elements']['#view_mode'], 'user');
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_block_alter')]
  public function theme_suggestions_block_alter(array &$suggestions, array &$variables): void {
    if (isset($variables['elements']['content']['#block_content']) && $variables['elements']['content']['#block_content'] instanceof BlockContentInterface) {
      $this->suggestions($suggestions, $variables['elements']['content']['#block_content'], $variables['elements']['content']['#view_mode'], 'block');
    }
  }

  /**
   * @inheritdoc
   * Implements hook_cohesion_templates_ENTITY_TYPE_base_hook_alter().
   */
  #[Hook('cohesion_templates_block_content_base_hook_alter')]
  public function block_content_base_hook_alter(&$base_hook): void {
    $base_hook = 'block';
  }

  /**
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_comment_alter')]
  public function theme_suggestions_comment_alter(array &$suggestions, array &$variables): void {
    $this->suggestions($suggestions, $variables['elements']['#comment'], $variables['elements']['#view_mode'], 'comment');
  }

  /**
   * @inheritdoc
   * Implements hook_form_BASE_FORM_ID_alter().
   *
   * Add the Site Studio template selector field to the advanced sidebar on node
   *   edit.
   */
  #[Hook('form_node_form_alter')]
  public function form_node_form_alter(&$form, FormStateInterface $form_state, $form_id) {
    $entity = $form_state->getFormObject()->getEntity();

    if ($entity->getEntityTypeId() == 'node') {

      if ($field = cohesion_templates_has_cohesion_template_selector_field($entity)) {
        $form['cohesion_template_selector_details'] = [
          '#type' => 'details',
          '#title' => 'Template',
          '#collapsible' => TRUE,
          '#collapsed' => TRUE,
          '#group' => 'advanced',
          '#weight' => 99,
          'content' => $form[$field->get('field_name')],
        ];
        unset($form[$field->get('field_name')]);
      }
    }
  }

  /**
   * Implements hook_config_translation_info_alter().
   */
  #[Hook('config_translation_info_alter')]
  public function config_translation_info_alter(&$info) {
    $info['cohesion_content_templates']['class'] = 'Drupal\cohesion_templates\ConfigTranslation\CohesionTemplatesMapper';
  }

}
