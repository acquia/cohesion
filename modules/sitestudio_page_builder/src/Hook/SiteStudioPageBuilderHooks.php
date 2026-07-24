<?php

namespace Drupal\sitestudio_page_builder\Hook;

use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\SettingsEndpointUtils;
use Drupal\cohesion_elements\CustomElementsService;
use Drupal\Component\Utility\Environment;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Routing\AdminContext;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\editor\Plugin\EditorManager;
use Drupal\entity_reference_revisions\EntityReferenceRevisionsFieldItemList;
use Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManager;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Hook implementations for Site Studio Page Builder.
 */
class SiteStudioPageBuilderHooks {

  public function __construct(
    protected RouteMatchInterface $route_match,
    protected AccountInterface $current_user,
    protected RequestStack $request,
    protected AdminContext $admin_context,
    protected LanguageManagerInterface $language_manager,
    protected EditorManager $editor_manager,
    protected RendererInterface $renderer,
    protected CohesionUtils $cohesion_utils,
    protected ImageBrowserUpdateManager $image_browser_update_manager,
    protected EventDispatcherInterface $event_dispatcher,
    protected SettingsEndpointUtils $settings_endpoint_utils,
    protected KeyValueFactoryInterface $key_value,
    protected CustomElementsService $custom_elements_service,
    protected SitestudioPageBuilderManager $sitestudio_page_builder_manager,
    protected EntityTypeManagerInterface $entity_type_manager,
  ) {}

  /**
   * Implements hook_page_attachments_alter().
   */
  #[Hook('page_attachments_alter')]
  public function page_attachments_alter(array &$attachments): void {
    // If the user has contextual links enabled, attach the component settings
    // tray overrides.
    $route_name = $this->route_match->getRouteName();
    $route = $this->route_match->getRouteObject();

    if (!$this->admin_context->isAdminRoute($route) &&
      $this->current_user->hasPermission(SITESTUDIO_PAGE_BUILDER_PAGE_BUILDER_PERMISSION) &&
      $route_name != 'cohesion_elements.component.preview'
    ) {
      $current_request = $this->request->getCurrentRequest();

      // If content lock module enabled, should this content be locked?
      if (sitestudio_page_builder_is_content_lock_enabled('node') &&
        $current_request &&
        $node = $current_request->attributes->get('node')
      ) {
        $lockService = \Drupal::service('content_lock');
        $currentLang = $this->language_manager->getCurrentLanguage(LanguageInterface::TYPE_CONTENT)->getId();
        // Fetch the lock and attach for JS.
        if ($node instanceof EntityInterface) {
          $lock = $lockService->fetchLock($node, $currentLang);
          $attachments['#attached']['drupalSettings']['cohesion']['content_lock'] = $lock;
          $attachments['#cache']['contexts'][] = 'user';
        }
      }

      $attachments['#attached']['library'][] = 'sitestudio_page_builder/editor-loader';

      $user_format_ids = array_keys(filter_formats($this->current_user));
      $definitions = $this->editor_manager->getDefinitions();

      // Filter the current user's formats to those that support inline editing.
      $formats = [];
      foreach ($user_format_ids as $format_id) {
        if ($editor = $this->entity_type_manager->getStorage('editor')->load($format_id)) {
          $editor_id = $editor->getEditor();
          if (isset($definitions[$editor_id]['supports_inline_editing']) && $definitions[$editor_id]['supports_inline_editing'] === TRUE) {
            $formats[] = $format_id;
          }
        }
      }

      // Get the attachments for all text editors that the user might use.
      $text_editor_attachments = $this->editor_manager->getAttachments($formats);

      // Patch the text format labels ("Full HTML") into the Drupal settings.
      if (isset($text_editor_attachments['drupalSettings']['editor']['formats'])) {
        foreach ($text_editor_attachments['drupalSettings']['editor']['formats'] as $key => $settings) {
          $format = $this->entity_type_manager->getStorage('filter_format')->load($key);
          $text_editor_attachments['drupalSettings']['editor']['formats'][$key]['label'] = $format->label();
        }
      }

      $attachments['#attached'] = array_merge_recursive($attachments['#attached'], $text_editor_attachments);

      // Add the max file size.
      $attachments['#attached']['drupalSettings']['cohesion']['upload_max_filesize'] = Environment::getUploadMaxSize();

      // Set 'cohesion' to be used as default editor.
      $attachments['#attached']['drupalSettings']['editor']['default'] = NULL;
      if (isset($attachments['#attached']['drupalSettings']['editor']['formats']['cohesion'])) {
        $attachments['#attached']['drupalSettings']['editor']['default'] = 'cohesion';
      }
      elseif (isset($attachments['#attached']['drupalSettings']['editor']['formats']) && is_array($attachments['#attached']['drupalSettings']['editor']['formats'])) {
        $last_format = end($attachments['#attached']['drupalSettings']['editor']['formats']);
        if ($last_format && isset($last_format['format'])) {
          $attachments['#attached']['drupalSettings']['editor']['default'] = $last_format['format'];
        }
      }

      // Load icon library for admin pages if it has been generated.
      $icon_lib_path = COHESION_CSS_PATH . '/cohesion-icon-libraries.css';
      if (file_exists($icon_lib_path)) {
        $attachments['#attached']['library'][] = 'cohesion/admin-icon-libraries';
      }
      // Add the token browser.
      if (isset($attachments['#token_browser'])) {
        // Build the token tree (token.module).
        // Check if it's an array of "allowed" tokens if not put into an array.
        if (!is_array($token_browser = $attachments['#token_browser'])) {
          $token_browser = [$attachments['#token_browser']];
        }

        $token_tree = [
          '#theme' => 'token_tree_link',
          '#token_types' => ($attachments['#token_browser'] == 'all') ? 'all' : $token_browser,
        ];

        // Render it using the service.
        $this->renderer->render($token_tree);
        // Attach the bootstrap fix to the form element.
        $attachments['#attached']['library'][] = 'cohesion/cohesion_token';
      }

      // Always attach match heights & parallax scrolling for VPB.
      $attachments['#attached']['library'][] = 'cohesion/global_libraries.matchHeight';
      $attachments['#attached']['library'][] = 'cohesion/global_libraries.cohMatchHeights';
      $attachments['#attached']['library'][] = 'cohesion/global_libraries.parallax_scrolling';
    }
  }

  /**
   * Implements hook_toolbar().
   */
  #[Hook('toolbar')]
  public function toolbar(): array {
    $items = [];

    if ($this->admin_context->isAdminRoute()) {
      return $items;
    }

    if ($this->current_user->hasPermission(SITESTUDIO_PAGE_BUILDER_PAGE_BUILDER_PERMISSION)) {
      $items['sitestudio'] = [
        '#type' => 'toolbar_item',
        '#weight' => -100,
        '#wrapper_attributes' => [
          'id' => 'ssa-builder-toggle',
          'class' => [
            'hidden',
            'ssa-builder-toggle',
          ],
        ],
        'tab' => [
          '#type' => 'html_tag',
          '#tag' => 'button',
          '#value' => t('Page builder'),
          '#attributes' => [
            'id' => 'coh-builder-btn',
            'class' => [
              'toolbar-icon',
            ],
          ],
        ],
      ];
    }

    return $items;
  }

  /**
   * Implements hook_theme().
   */
  #[Hook('theme')]
  public function theme($existing, $type, $theme, $path): array {
    return [
      'sitestudio_build' => [
        'template' => 'sitestudio-build',
        'base hook' => 'page',
        'variables' => [
          'build' => NULL,
        ],
      ],
    ];
  }

  /**
   * Implements hook_entity_display_build_alter().
   */
  #[Hook('entity_display_build_alter')]
  public function entity_display_build_alter(&$build, $context): void {
    $entity = NULL;
    if (isset($context['entity']) && $context['entity'] instanceof ContentEntityInterface) {
      $entity = $context['entity'];
    }

    // Add the page builder data attribute to the canvas element if the user has
    // the right permission
    if ($entity && $entity->access('update') &&
      $this->current_user->hasPermission(SITESTUDIO_PAGE_BUILDER_PAGE_BUILDER_PERMISSION)
    ) {
      foreach ($build as &$element) {
        if (isset($element['#field_type']) && $element['#field_type'] == 'cohesion_entity_reference_revisions' &&
          $element['#items'] instanceof EntityReferenceRevisionsFieldItemList
        ) {
          foreach ($element['#items'] as &$item) {
            /** @var \Drupal\cohesion_elements\Plugin\Field\FieldType\CohesionEntityReferenceRevisionsItem $item */
            // Page builder is allowed only on page containing components only.
            // Only add attributes if the entity returned is the current entity.
            if ($item->getFieldDefinition()->getSetting('access_elements') !== 1 &&
              $this->sitestudio_page_builder_manager->shouldEnablePageBuilder() === $entity
            ) {
              $element['#attributes']['data-ssa-canvas'] = 'cohcanvas-' . $item->getValue()['target_id'];
              $element['#cache']['contexts'][] = 'user.permissions';
            }
          }
        }
      }
    }
  }

  /**
   * Implements hook_page_attachments().
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  #[Hook('page_attachments')]
  public function page_attachments(array &$attachments): void {
    $is_admin = $this->cohesion_utils->isAdminTheme();
    if (!$is_admin && $this->current_user->hasPermission(SITESTUDIO_PAGE_BUILDER_PAGE_BUILDER_PERMISSION)) {
      // Url collection for js app
      $event = new CohesionJsAppUrlsEvent();
      $this->event_dispatcher->dispatch($event, $event::FRONTEND_URL);
      $attachments['#attached']['drupalSettings']['cohesion']['urls'] = $event->getUrls();

      // Image browser page attachments.
      $this->image_browser_update_manager->sharedPageAttachments($attachments['#attached'], 'content');

      $attachments['#attached']['drupalSettings']['cohesion']['permissions'] = $this->settings_endpoint_utils->dx8PermissionsList();

      // Get the apiUrls
      $apiUrls = $this->key_value->get('cohesion.assets.static_assets')->get('api-urls');

      // Patch the custom element data in.
      $apiUrls = $this->custom_elements_service->patchApiUrls($apiUrls);

      // And attach.
      $attachments['#attached']['drupalSettings']['cohesion']['api_urls'] = $apiUrls;
    }
  }

}
