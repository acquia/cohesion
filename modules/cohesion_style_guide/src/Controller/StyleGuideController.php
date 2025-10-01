<?php

namespace Drupal\cohesion_style_guide\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion\Services\LocalFilesManager;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_base_styles\Entity\BaseStyles;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler;
use Drupal\Component\Uuid\UuidInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Extension\ExtensionPathResolver;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CohesionEndpointController.
 *
 * Returns Drupal data to Angular (views, blocks, node lists, etc).
 * See function index() for the entry point.
 *
 * @package Drupal\cohesion\Controller
 */
class StyleGuideController extends ControllerBase {

  /**
   * The library discovery service.
   *
   * @var \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected $themeManager;


  /**
   * @var \Drupal\cohesion\Services\LocalFilesManager*/
  protected $localFilesManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * @var \Drupal\Core\TempStore\PrivateTempStore
   */
  protected $tempStore;

  /**
   * @var \Drupal\Component\Uuid\UuidInterface
   */
  protected $uuid;

  /**
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @var \Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler
   */
  protected $styleGuideManagerHandler;

  /**
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * StyleGuideController constructor.
   *
   * @param \Drupal\Core\Theme\ThemeManagerInterface $theme_manager
   * @param \Drupal\cohesion\Services\LocalFilesManager $local_files_manager
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $temp_store_factory
   * @param \Drupal\Component\Uuid\UuidInterface $uuid_manager
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   * @param \Drupal\cohesion_style_guide\Services\StyleGuideManagerHandler $style_guide__manager_handler
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extensionPathResolver
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   */
  public function __construct(
    ThemeManagerInterface $theme_manager,
    LocalFilesManager $local_files_manager,
    EntityRepositoryInterface $entity_repository,
    PrivateTempStoreFactory $temp_store_factory,
    UuidInterface $uuid_manager,
    UsageUpdateManager $usage_update_manager,
    StyleGuideManagerHandler $style_guide__manager_handler,
    ExtensionPathResolver $extensionPathResolver,
    FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    $this->themeManager = $theme_manager;
    $this->localFilesManager = $local_files_manager;
    $this->entityRepository = $entity_repository;
    $this->tempStore = $temp_store_factory->get('cohesion');
    $this->uuid = $uuid_manager;
    $this->usageUpdateManager = $usage_update_manager;
    $this->styleGuideManagerHandler = $style_guide__manager_handler;
    $this->fileUrlGenerator = $fileUrlGenerator;
    $this->extensionPathResolver = $extensionPathResolver;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('theme.manager'),
      $container->get('cohesion.local_files_manager'),
      $container->get('entity.repository'),
      $container->get('tempstore.private'),
      $container->get('uuid'),
      $container->get('cohesion_usage.update_manager'),
      $container->get('cohesion_style_guide.style_guide_handler'),
      $container->get('extension.path.resolver'),
      $container->get('file_url_generator')
    );
  }

  /**
   * Constructs a page with placeholder content.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function styleGuideCSS(Request $request) {

    $type = $request->get('t');
    $uuid = $request->get('uuid');

    $css = $this->tempStore->get('coh-preview-sgm-' . $type . '-' . $uuid);

    $response = new Response();
    $response->setContent($css);
    $response->setStatusCode(Response::HTTP_OK);
    $response->headers->set('Content-Type', 'text/css');
    return $response;
  }

  /**
   *
   */
  public function buildStyleGuidePreview(Request $request) {
    $preview_json_values = $request->getContent();
    $active_theme = $this->themeManager->getActiveTheme();

    // Set the style guide manager preview values.
    $preview_sgm_values = &drupal_static('coh_preview_tokens_sgm_' . $active_theme->getName());
    $preview_sgm_values = $preview_json_values;
    $preview_decoded_json = json_decode($preview_json_values);
    $in_use_entities = [];
    // The current active theme values.
    $style_guide_manager = json_decode($this->styleGuideManagerHandler->getStyleGuideManagerJson($active_theme->getName()));
    // Get all entities that needs to be build for the style guides that has
    // been modified.
    if (property_exists($preview_decoded_json, 'model') && property_exists($preview_decoded_json, 'changedFields')) {
      foreach ($preview_decoded_json->model as $style_guide_uuid => $style_guide_values) {

        // Check whether some value(s) of the current style guide in the loop
        // has been changed and flag it $getInuseStyleGuide = TRUE so we can
        // retrieve where this style guide is in use and send the related
        // entities to the Site Studio API to be generated for the preview.
        $getInuseStyleGuide = FALSE;
        foreach ($style_guide_values as $style_guide_value_uuid => $style_guide_value) {
          // A values is concidered to have changed if it's in the
          // `changedFields` array of a sent values and if in the currently
          // saved values it does not exist or the value is different.
          if (in_array('model.' . $style_guide_uuid . '.' . $style_guide_value_uuid, $preview_decoded_json->changedFields)
          && (!property_exists($style_guide_manager, 'model') || !property_exists($style_guide_manager->model, $style_guide_uuid)
            || !property_exists($style_guide_manager->model->$style_guide_uuid, $style_guide_value_uuid) || $style_guide_manager->model->$style_guide_uuid->$style_guide_value_uuid != $style_guide_value)) {
            $getInuseStyleGuide = TRUE;
            break;
          }
        }

        $style_guide = $this->entityRepository->loadEntityByUuid('cohesion_style_guide', $style_guide_uuid);
        if ($getInuseStyleGuide && $style_guide) {
          $in_use_entities = array_merge($in_use_entities, $this->usageUpdateManager->getInUseEntitiesList($style_guide));
        }
      }
    }

    $forms = [];
    foreach ($in_use_entities as $entity_uuid => $entity_type) {
      $entity = $this->entityRepository->loadEntityByUuid($entity_type, $entity_uuid);

      if ($entity instanceof CustomStyle || $entity instanceof BaseStyles) {
        $api_plugin = $entity->getApiPluginInstance();
        if ($api_plugin) {
          $api_plugin->setEntity($entity);
          $forms = array_merge($forms, $api_plugin->getForms());
        }
      }
    }

    /** @var \Drupal\cohesion\Plugin\Api\StylesApi $send_to_api */
    $send_to_api = \Drupal::service('plugin.manager.api.processor')
      ->createInstance('styles_api');
    $send_to_api->setForms($forms);
    $send_to_api->setWithTimestamp(FALSE);
    if (!$send_to_api->sendWithoutSave()) {
      return new CohesionJsonResponse([
        'status' => 'error',
        'data' => $send_to_api->getData(),
      ], 400);
    }

    $uuid = $this->tempStore->get('coh-sgm-preview-uuid');
    if (!$uuid) {
      $uuid = $this->uuid->generate();
      $this->tempStore->set('coh-sgm-preview-uuid', $uuid);
    }

    $theme_css = '';
    $theme_css_data = $send_to_api->getResponseStyles('theme', $active_theme->getName(), TRUE);
    if ($theme_css_data) {
      $theme_css = (string) \Drupal::service('twig')
        ->renderInline($theme_css_data);
    }

    $response = [];

    if ($this->tempStore->get('coh-preview-sgm-theme-' . $uuid) != $theme_css) {
      $this->tempStore->set('coh-preview-sgm-theme-' . $uuid, $theme_css);
      $theme_css_path = $this->fileUrlGenerator->generateString($this->localFilesManager->getStyleSheetFilename('theme', $active_theme->getName()));
      $theme_css_preview = Url::fromRoute('cohesion_style_guide.preview.css')
        ->setRouteParameter('t', 'theme')
        ->setRouteParameter('uuid', $uuid)
        ->toString();
      $response[$theme_css_path] = $theme_css_preview;
    }

    return new CohesionJsonResponse([
      'status' => 'success',
      'data' => $response,
    ]);
  }

  /**
   * Renders the SGM preview full page wrapper.
   * See: templates/preview-full.html.twig.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return array
   */
  public function previewFull(Request $request) {
    $page = [
      '#theme' => 'component_preview_full',
      '#attached' => [
        'library' => [
          'cohesion/cohesion-admin-scripts',
          'cohesion/cohesion-admin-styles',
        ],
        'drupalSettings' => [
          'cohesion' => [
            'formGroup' => 'style_guide',
            'formId' => 'preview',
            'drupalFormId' => 'cohPreviewForm',
            'canvas_preview_css' => $this->extensionPathResolver->getPath('module', 'cohesion_elements') . '/css/canvas-preview.css',
            'canvas_preview_js' => $this->extensionPathResolver->getPath('module', 'cohesion_elements') . '/js/canvas-preview.js',
          ],
          'cohOnInitForm' => \Drupal::service('settings.endpoint.utils')->getCohFormOnInit('style_guide', 'preview'),
        ],
      ],
    ];

    _cohesion_shared_page_attachments($page);

    return $page;
  }

}
