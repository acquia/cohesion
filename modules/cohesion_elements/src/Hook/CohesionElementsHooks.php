<?php

namespace Drupal\cohesion_elements\Hook;

use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableRevisionableStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\ByteSizeMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Utility\Error;
use Drupal\Core\Render\Element;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\cohesion_elements\CustomComponentsService;

/**
 * Hook implementations for Cohesion elements.
 */
class CohesionElementsHooks {

  public function __construct(
    protected readonly KeyValueFactoryInterface $key_value,
    protected FileUrlGeneratorInterface $file_url_generator,
    protected ConfigFactoryInterface $config_factory,
    protected CustomComponentsService $custom_components_service,
    protected LoggerChannelFactoryInterface $logger_channel_factory,
    protected CurrentRouteMatch $current_route_match,
    protected AdminContext $admin_context,
    protected AccountInterface $current_user,
    protected ThemeManagerInterface $theme_manager,
    protected EntityFieldManagerInterface $entity_field_manager,
    protected EntityTypeManagerInterface $entity_type_manager,
    protected ModuleHandlerInterface $module_handler,
    protected ImageFactory $image_factory,
  ) {}

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help($route_name) {
    switch ($route_name) {
      // Main module help for the cohesion_custom_styles module.
      case 'help.page.cohesion_elements':
        $output = '';
        $output .= '<h3>' . t('About') . '</h3>';
        $output .= '<p>' . t('This module defines the component and helper config entities for creating reusable design patterns within Site Studio.') . '</p>';
        $output .= '<p><ul>';
        $output .= '  <li>Component configuration entity and supporting forms.</li>';
        $output .= '  <li>Template helper configuration entity and supporting forms.</li>';
        $output .= '  <li>Site Studio API integration.</li>';
        $output .= '  <li>Custom Views query plugin for listing components and helpers.</li>';
        $output .= '  <li>Layout field plugin for adding Site Studio layouts to fieldable entities.</li>';
        $output .= '</ul></p>';
        $output .= '<p><a href="https://docs.acquia.com/" target="_blank">https://docs.acquia.com/</a></p>';
        return $output;

      default:
    }
  }

  /**
   * Helper function to process and build library assets.
   *
   * @param array $assets
   *   The asset array for CSS or JS.
   * @param string $subpath
   *   The path to the component parent folder, relative to root. This is
   *   expected to have a forward slash at the beginning and end already.
   */
  private function component_build_library(array $assets, string $subpath): array {
    $processed = [];
    foreach ($assets as $asset_file => $asset_data) {
      // Allow external assets to use absolute path.
      if (!empty($asset_data['type']) && $asset_data['type'] == 'external') {
        $asset_path = $asset_file;
      }
      else {
        $asset_path = $subpath . $asset_file;
      }
      $processed[$asset_path] = $asset_data;
    }

    return $processed;
  }

  /**
   * Implements hook_library_info_alter().
   */
  #[Hook('library_info_alter')]
  public function library_info_alter(&$libraries, $extension): void {
    global $base_url;

    if ($extension == 'cohesion') {
      // Path is relative to Drupal root if begins with '/'.
      // Otherwise, relative from module/theme root.
      if ($cohesion_asset_libraries = $this->key_value->get('cohesion.elements.asset.libraries')->getAll()) {
        foreach ($cohesion_asset_libraries as $library_key => $library_info) {
          $libraries[$library_key] = [];

          // Add the js assets to the library.
          if (isset($library_info['js'])) {
            foreach ($library_info['js'] as $library) {
              $url = $this->file_url_generator->generateAbsoluteString($library['asset_url']);
              $asset_url = (isset($url) && (str_contains($url, $base_url))) ? str_replace($base_url, '', $url) : $url;
              // Replace StreamWrapper with its base path for translation.
              // see _locale_parse_js_file in locale.module.
              $library['asset_url'] = $asset_url;
              $libraries[$library_key]['js'][$library['asset_url']] = [
                'weight' => (isset($library['weight']) && $library['weight'] < 0) ? $library['weight'] : 0,
                'minified' => $library['minified'] ?? FALSE,
              ];
            }
          }

          // Add the CSS assets to the library.
          if (isset($library_info['css'])) {
            foreach ($library_info['css'] as $library) {
              $weight = $library['weight'] ?? 'theme';

              $libraries[$library_key]['css'][$weight][$library['asset_url']] = [
                'minified' => $library['minified'] ?? FALSE,
              ];
            }
          }

          // Add any library dependencies.
          if (isset($library_info['dependencies'])) {
            foreach ($library_info['dependencies'] as $dependency) {
              $libraries[$library_key]['dependencies'][] = $dependency;
            }
          }

          // Check for incomplete definition.
          if (is_array($libraries[$library_key]) && count($libraries[$library_key]) == 0) {
            unset($libraries[$library_key]);
          }

          // Remove matchHeights & parallax from libraries if disabled as it can
          // be a dependency of another library.
          if ($this->config_factory->get('cohesion.frontend.settings')->get('js.matchHeight') == 0) {
            unset($libraries['global_libraries.matchHeight']);
            unset($libraries['global_libraries.cohMatchHeights']);
            unset($libraries['global_libraries.cohContainerMatchHeights']);
          }

          if ($this->config_factory->get('cohesion.frontend.settings')->get('js.parallax_scrolling') == 0) {
            unset($libraries['global_libraries.parallax_scrolling']);
          }
        }
      }

      try {
        $custom_components = $this->custom_components_service->getComponents();
      }
      catch (\Exception $exception) {
        $this->logger_channel_factory->get('cohesion_elements.custom_components')->error(
          $exception->getMessage(),
          Error::decodeException($exception)
        );
      }
      if (isset($custom_components)) {
        foreach ($custom_components as $id => $component) {
          $library_id = 'custom-component-' . str_replace('_', '-', $id);
          // Build the library
          if (!empty($component['css'])) {
            $libraries[$library_id]['css']['component'] = $this->component_build_library($component['css'], $component['subpath']);
          }
          if (!empty($component['js'])) {
            $libraries[$library_id]['js'] = $this->component_build_library($component['js'], $component['subpath']);
          }
          if ($component['dependencies']) {
            $libraries[$library_id]['dependencies'] = $component['dependencies'];
          }
        }
      }
    }
  }

  /**
   * Copy parent context to current context (for tokens).
   *
   * @param $variables
   */
  #[Hook('preprocess_cohesion_elements_component')]
  public function preprocess_cohesion_elements_component(&$variables): void {
    foreach ($variables['parentContext'] as $key => $value) {
      // List of keys from parent context to exclude adding to current context.
      $exclude_key_list = [
        'componentFieldsValues',
        'template',
        'coh_repeater_val',
        'parentContext',
        // Preserve the child component's own path context so nested
        // edit-in-place mapping can resolve the correct branch.
        'componentUuidPath',
      ];

      if (!in_array($key, $exclude_key_list) && !$value instanceof Attribute) {
        $variables[$key] = $value;
      }
    }
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme(): array {
    $themes = [
      'cohesion_layout' => [
        'render element' => 'element',
      ],
      'field__component_content__layout_canvas' => [
        'template' => 'component-content-field',
        'base hook' => 'field',
      ],
      'component' => [
        'template' => 'cohesion-component-template',
        'render element' => 'children',
        'variables' => [
          'content' => '',
          'parentContext' => [],
          'parentIsComponent' => FALSE,
          'componentFieldsValues' => [],
          'componentUuid' => '',
          'componentUuidPath' => [],
          'template' => '',
        ],
        'preprocess functions' => [
          'preprocess_cohesion_elements_component',
          'contextual_preprocess',
        ],
      ],
      'component_preview_full' => [
        'template' => 'canvas-preview-full',
        'base hook' => 'page',
      ],
      'page__cohesionapi__component__preview' => [
        'template' => 'page--cohesionapi--component--preview',
        'base hook' => 'page',
        'preprocess functions' => [
          'preprocess_cohesion_preview_page',
        ],
      ],
      'form_color_class_radios' => [
        'render element' => 'element',
      ],
    ];

    try {
      $custom_components = $this->custom_components_service->getComponents();

      foreach($custom_components as $id => $component) {
        if (isset($component['template'])) {
          $themes['custom_component_' . str_replace('-', '_', $id)] = [
            'variables' => [
              'field' => NULL,
              'dropzones' => [],
            ],
            'template' => $component['template'],
            'path' => rtrim($component['subpath'], '/'),
            'render comp' => 'children',
          ];
        }
      }
    }
    catch (\Exception $exception) {
      $this->logger_channel_factory->get('cohesion_elements.custom_components')->error($exception->getMessage(), Error::decodeException($exception));
    }

    // Default custom component theme
    // When a template is not provided
    $themes['custom_component'] = [
      'variables' => [
        'field' => NULL,
        'attributes' => NULL,
        'dropzones' => NULL,
        'html_template' => NULL,
      ],
      'template' => 'custom-component',
      'render comp' => 'children',
    ];

    return $themes;
  }

  /**
   * Implements hook_page_attachments().
   */
  #[Hook('page_attachments')]
  public function page_attachments(array &$attachments): void {
    // Restrict access to certain component types depending on the route.
    $route_name = $this->current_route_match->getRouteName();
    $allowed_pages = [
      'entity.cohesion_master_templates.edit_form',
      'entity.cohesion_content_templates.edit_form',
      'entity.cohesion_view_templates.edit_form',
      'entity.cohesion_menu_templates.edit_form',
    ];

    if (in_array($route_name, $allowed_pages)) {
      $attachments['#attached']['drupalSettings']['cohesion']['restrictedComponents'] = [
        'entity_type_access' => 'dx8_templates',
        'bundle_access' => 'dx8_templates',
      ];
    }

    // If the user has contextual links enabled, attach the component settings
    // tray overrides.
    $route = $this->current_route_match->getRouteObject();
    if (!$this->admin_context->isAdminRoute($route) &&
      $this->current_user->hasPermission('access contextual links') &&
      $this->current_user->hasPermission('access components') &&
      $route_name != 'cohesion_elements.component.preview') {
      $attachments['#attached']['library'][] = 'cohesion_elements/settings-tray';
    }
  }

  /**
   * @inheritdoc
   * Implements hook_theme_suggestions_HOOK_alter().
   */
  #[Hook('theme_suggestions_component_alter')]
  public function theme_suggestions_component_alter(array &$suggestions, array &$variables): void {
    if (isset($variables['template'])) {
      $suggestions[] = $variables['template'] . '__' . $this->theme_manager->getActiveTheme()->getName();
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for field templates.
   */
  #[Hook('preprocess_field')]
  public function preprocess_field(&$variables): void {
    $element = $variables['element'];
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $element['#object'];

    if (isset($variables['attributes']['data-quickedit-field-id']) && $entity instanceof ComponentContent) {
      unset($variables['attributes']['data-quickedit-field-id']);
    }
  }

  /**
   * Implements hook_theme_registry_alter().
   */
  #[Hook('theme_registry_alter')]
  public function theme_registry_alter(&$theme_registry): void {
    if (isset($theme_registry['field']['preprocess functions'])) {
      foreach ($theme_registry['field']['preprocess functions'] as $key => $value) {
        if ($value == 'cohesion_elements_preprocess_field') {
          unset($theme_registry['field']['preprocess functions'][$key]);
          $theme_registry['field']['preprocess functions'][] = 'cohesion_elements_preprocess_field';
        }
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   *
   * Indicate unsupported multilingual Site Studio field configuration.
   *
   * Add a warning that Site Studio fields can not be translated.
   * Switch to error if a Site Studio field is marked as translatable.
   */
  #[Hook('form_language_content_settings_form_alter')]
  public function form_language_content_settings_form_alter(&$form): void {
    // Without it Site Studio message are meaningless.
    if (!\Drupal::hasService('content_translation.manager')) {
      return;
    }

    $content_translation_manager = \Drupal::service('content_translation.manager');
    $message_display = 'warning';
    $message_text = t('(* unsupported) Layout canvas fields do not support translation.');
    $map = $this->entity_field_manager->getFieldMapByFieldType('entity_reference_revisions');
    foreach ($map as $entity_type_id => $info) {
      if (!$content_translation_manager->isEnabled($entity_type_id)) {
        continue;
      }
      $field_storage_definitions = $this->entity_field_manager->getFieldStorageDefinitions($entity_type_id);

      /** @var \Drupal\Core\Field\FieldStorageDefinitionInterface $storage_definition */
      foreach ($field_storage_definitions as $name => $storage_definition) {
        if ($storage_definition->getSetting('target_type') && $storage_definition->getSetting('target_type') == 'cohesion_layout') {

          // For configurable fields, check all bundles on which the field
          // exists, for base fields that are translatable, check all bundles,
          // untranslatable base fields do not show up at all.
          $bundles = [];
          if ($storage_definition instanceof FieldStorageConfigInterface) {
            $bundles = $storage_definition->getBundles();
          }
          elseif ($storage_definition->isTranslatable()) {
            $bundles = Element::children($form['settings'][$entity_type_id]);
          }
          foreach ($bundles as $bundle) {
            if (!$content_translation_manager->isEnabled($entity_type_id, $bundle)) {
              continue;
            }

            // Update the label and if the Site Studio field is translatable,
            // display an error message instead of just a warning.
            if (isset($form['settings'][$entity_type_id][$bundle]['fields'][$name]['#label'])) {
              $form['settings'][$entity_type_id][$bundle]['fields'][$name]['#label'] = t('@field_label (* unsupported)', ['@field_label' => $form['settings'][$entity_type_id][$bundle]['fields'][$name]['#label']]);
            }
            if (!empty($form['settings'][$entity_type_id][$bundle]['fields'][$name]['#default_value'])) {
              $message_display = 'error';
            }

          }
        }
      }
    }

    // Update the description on the hide untranslatable fields' checkbox.
    if (isset($form['settings']['cohesion_layout'])) {
      $cohesion_layout_untranslatable_hide_description = t('Layout canvas that are used in moderated content requires non-translatable fields to be edited in the original language form and this must be checked.');
      foreach (Element::children($form['settings']['cohesion_layout']) as $bundle) {
        if (!empty($form['settings']['cohesion_layout'][$bundle]['settings']['content_translation']['untranslatable_fields_hide'])) {
          $form['settings']['cohesion_layout'][$bundle]['settings']['content_translation']['untranslatable_fields_hide']['#description'] = $cohesion_layout_untranslatable_hide_description;
        }
      }
    }

    $form['settings']['layout_canvas_message'] = [
      '#type' => 'container',
      '#markup' => $message_text,
      '#attributes' => [
        'class' => ['messages messages--' . $message_display],
      ],
      '#weight' => 0,
    ];
  }

  /**
   * Implements hook_form_FORM_ID_alter() for 'field_ui_field_storage_add_form'.
   */
  #[Hook('form_field_ui_field_storage_add_form_alter')]
  public function form_field_ui_field_storage_add_form_alter(array &$form): void {
    if (empty($form['add']['new_storage_type'])) {
      return;
    }

    $keys_flipped = array_flip([
      'cohesion_entity_reference_revisions',
      'field_ui:entity_reference_revisions:cohesion_layout',
      'field_ui:cohesion_entity_reference_revisions:paragraph',
    ]);

    $form['add']['new_storage_type'] = array_diff_key($form['add']['new_storage_type'], $keys_flipped);

    // Remove from nested option groups (skip Form API render elements).
    if (!empty($form['add']['new_storage_type']['#options'])) {
      array_walk($form['add']['new_storage_type']['#options'], function (&$group, $key) use ($keys_flipped) {
        if (is_array($group) && (!is_string($key) || $key[0] !== '#')) {
          $group = array_diff_key($group, $keys_flipped);
        }
      });
    }

    // Remove from wrapper structures.
    foreach (['group_field_options_wrapper', 'field_options_wrapper'] as $wrapper) {
      if (!empty($form[$wrapper]['fields'])) {
        $form[$wrapper]['fields'] = array_diff_key($form[$wrapper]['fields'], $keys_flipped);
      }
    }
  }

  /**
   * Implements hook_field_ui_preconfigured_options_alter().
   */
  #[Hook('field_ui_preconfigured_options_alter')]
  public function field_ui_preconfigured_options_alter(array &$options, string $field_type): void {
    if ($field_type === 'entity_reference_revisions' && array_key_exists('cohesion_layout', $options)) {
      unset($options['cohesion_layout']);
    }
  }

  /**
   * Implements hook_preprocess_image_widget().
   */
  #[Hook('preprocess_image_widget')]
  public function preprocess_image_widget(&$variables): void {
    $element = $variables['element'];

    if (isset($element['#preview_image_style']) && $element['#preview_image_style'] == 'dx8_component_preview' && !empty($element['fids']['#value'])) {
      $file = reset($element['#files']);
      $element['file_' . $file->id()]['filename']['#suffix'] = ' <span class="file-size">(' . ByteSizeMarkup::create($file->getSize()) . ')</span> ';
      $file_variables = [
        'style_name' => $element['#preview_image_style'],
        'uri' => $file->getFileUri(),
      ];

      // Determine image dimensions.
      if (isset($element['#value']['width']) && isset($element['#value']['height'])) {
        $file_variables['width'] = $element['#value']['width'];
        $file_variables['height'] = $element['#value']['height'];
      }
      else {
        $image = $this->image_factory->get($file->getFileUri());
        if ($image->isValid()) {
          $file_variables['width'] = $image->getWidth();
          $file_variables['height'] = $image->getHeight();
        }
        else {
          $file_variables['width'] = $file_variables['height'] = NULL;
        }
      }

      $element['preview'] = [
        '#weight' => -10,
        '#theme' => 'image_style',
        '#width' => $file_variables['width'],
        '#height' => $file_variables['height'],
        '#style_name' => $file_variables['style_name'],
        '#uri' => $file_variables['uri'],
      ];

      // Store the dimensions in the form so the file doesn't have to be
      // accessed again. This is important for remote files.
      $element['width'] = [
        '#type' => 'hidden',
        '#value' => $file_variables['width'],
      ];
      $element['height'] = [
        '#type' => 'hidden',
        '#value' => $file_variables['height'],
      ];
    }

    $variables['data'] = [];
    foreach (Element::children($element) as $child) {
      $variables['data'][$child] = $element[$child];
    }
  }

  /**
   * Implements hook_menu_local_actions_alter().
   */
  #[Hook('menu_local_actions_alter')]
  public function menu_local_actions_alter(&$local_actions): void {

    $action_routes = [
      'entity.cohesion_component.toggle_components_groups',
      'entity.cohesion_helper.toggle_helpers_groups',
      'entity.component_content.toggle_component_content_groups',
    ];

    foreach($action_routes as $action_route) {
      // Add class to component admin link actions.
      if (isset($local_actions[$action_route])) {
        $local_actions[$action_route]['options']['attributes']['class'][] = 'coh-toggle-accordion';
        $local_actions[$action_route]['options']['attributes']['class'][] = 'open';
        $local_actions[$action_route]['options']['attributes']['role'][] = 'button';
      }
    }
  }

  /**
   * Implements hook_entity_revision_create().
   */
  #[Hook('entity_revision_create')]
  public function entity_revision_create(ContentEntityInterface $new_revision, ContentEntityInterface $entity, $keep_untranslatable_fields): void {
    $storage = $this->entity_type_manager->getStorage($entity->getEntityTypeId());
    foreach ($entity->getFieldDefinitions() as $field_name => $field_definition) {
      if ($field_definition->getType() == 'cohesion_entity_reference_revisions' && !$field_definition->isTranslatable()) {
        $target_entity_type_id = $field_definition->getSetting('target_type');
        if ($this->entity_type_manager->getDefinition($target_entity_type_id)->get('entity_revision_parent_id_field')) {

          // The default implementation copied the values from the current
          // default revision into the field since it is not translatable.
          // Take the originally referenced entity, create a new revision
          // of it and set that instead on the new entity revision.
          $active_langcode = $entity->language()->getId();
          $target_storage = $this->entity_type_manager->getStorage($target_entity_type_id);
          if ($target_storage instanceof TranslatableRevisionableStorageInterface) {

            $items = $entity->get($field_name);
            $translation_items = NULL;
            if (!$new_revision->isDefaultTranslation() && $storage instanceof TranslatableRevisionableStorageInterface) {
              $translation_items = $items;
              $items = $storage->load($new_revision->id())->get($field_name);
            }

            $values = [];
            foreach ($items as $delta => $item) {
              // Use the item from the translation if it exists.
              // If we have translation items, use that if one with the matching
              // target id exists.
              if ($translation_items) {
                foreach ($translation_items as $translation_item) {
                  if ($item->target_id == $translation_item->target_id) {
                    $item = $translation_item;
                    break;
                  }
                }
              }

              /** @var \Drupal\Core\Entity\ContentEntityInterface $target_entity */
              $target_entity = $item->entity;
              if (!$target_entity->hasTranslation($active_langcode)) {
                $target_entity->addTranslation($active_langcode, $target_entity->toArray());
              }
              $target_entity = $item->entity->getTranslation($active_langcode);
              $revised_entity = $target_storage->createRevision($target_entity, $new_revision->isDefaultRevision(), $keep_untranslatable_fields);

              // Restore the revision ID.
              $revision_key = $revised_entity->getEntityType()->getKey('revision');
              $revised_entity->set($revision_key, $revised_entity->getLoadedRevisionId());
              $values[$delta] = $revised_entity;
            }
            $new_revision->set($field_name, $values);
          }
        }
      }
    }
  }

  /**
   * Implements hook_user_cancel().
   */
  #[Hook('user_cancel')]
  public function user_cancel($account, $method): void {
    switch ($method) {
      case 'user_cancel_block_unpublish':

        // Unpublish component contents (current revisions).
        $this->module_handler
          ->loadInclude('cohesion_elements', 'inc', 'cohesion_elements.admin');
        $component_content = $this->entity_type_manager
          ->getStorage('component_content')
          ->getQuery()
          ->accessCheck(TRUE)
          ->condition('uid', $account->id())
          ->execute();
        component_contents_mass_update($component_content, [
          'status' => 0,
        ], NULL, TRUE);
        break;

      case 'user_cancel_reassign':

        // Anonymize component content (current revisions).
        $this->module_handler
          ->loadInclude('cohesion_elements', 'inc', 'cohesion_elements.admin');
        $component_content = $this->entity_type_manager
          ->getStorage('component_content')
          ->getQuery()
          ->accessCheck(TRUE)
          ->condition('uid', $account->id())
          ->execute();
        component_contents_mass_update($component_content, [
          'uid' => 0,
        ], NULL, TRUE);
        break;
    }
  }

  /**
   * Implements hook_ENTITY_TYPE_predelete() for user entities.
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  #[Hook('user_predelete')]
  public function user_predelete($account): void {
    // Delete component content & revisions.
    $ids = $this->entity_type_manager
      ->getStorage('component_content')
      ->getQuery()
      ->condition('uid', $account->id())
      ->accessCheck(FALSE)
      ->execute();
    $storage_controller = $this->entity_type_manager->getStorage('component_content');
    $component_contents = $storage_controller->loadMultiple($ids);
    $storage_controller->delete($component_contents);
  }

}
