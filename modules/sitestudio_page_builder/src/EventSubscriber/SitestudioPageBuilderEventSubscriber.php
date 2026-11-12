<?php

namespace Drupal\sitestudio_page_builder\EventSubscriber;

use Drupal\cohesion\CohesionApiClient;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion_elements\Event\CohesionLayoutViewBuilderEvent;
use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Event subscriber for the visual page builder.
 *
 */
class SitestudioPageBuilderEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The current user account.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $user;

  /**
   * Current route match service
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * The current request
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $currentRequest;

  /**
   * The library discovery service
   *
   * @var \Drupal\Core\Asset\LibraryDiscoveryInterface
   */
  protected $libraryDiscovery;

  /**
   * The page builder manager
   *
   * @var \Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface
   */
  protected $pageBuilderManager;

  /**
   * The module handler service
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * The cohesion utils helper.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * The cohesion api client service.
   *
   * @var \Drupal\cohesion\CohesionApiClient
   */
  protected $cohesionApiClient;

  /**
   * The entity repository service
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * File URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * SitestudioPageBuilderEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *  The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $user
   *  The current user account.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   * Current rout match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *  Current route match service.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *  Library discovery service
   * @param \Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface $page_builder_manager
   *  The page builder manager
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *  The module handler service
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   *  The cohesion utils service
   * @param \Drupal\cohesion\CohesionApiClient $cohesion_api_client
   *  The cohesion api client service
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *  Entity repository service
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *  File URL generator service.
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    AccountInterface $user,
    RouteMatchInterface $current_route_match,
    RequestStack $request_stack,
    LibraryDiscoveryInterface $library_discovery,
    SitestudioPageBuilderManagerInterface $page_builder_manager,
    ModuleHandlerInterface $module_handler,
    CohesionUtils $cohesion_utils,
    CohesionApiClient $cohesion_api_client,
    EntityRepositoryInterface $entity_repository,
    FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->user = $user;
    $this->currentRouteMatch = $current_route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->libraryDiscovery = $library_discovery;
    $this->pageBuilderManager = $page_builder_manager;
    $this->moduleHandler = $module_handler;
    $this->cohesionUtils = $cohesion_utils;
    $this->cohesionApiClient = $cohesion_api_client;
    $this->entityRepository = $entity_repository;
    $this->fileUrlGenerator = $fileUrlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      CohesionJsAppUrlsEvent::FRONTEND_URL => 'registerFrontendUrls',
      CohesionLayoutViewBuilderEvent::ALTER => 'alterViewBuilder',
    ];
  }

  /**
   * Registers urls for frontend builder to work
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerFrontendUrls(CohesionJsAppUrlsEvent $event) {
    // Each build method has its own url derivating from the entity
    // canonical url @see \Drupal\sitestudio_page_builder\Routing\Route
    $query_param = $this->currentRequest->query->all();

    if ($entity = $this->pageBuilderManager->shouldEnablePageBuilder()) {
      $params = [
        $entity->getEntityTypeId() => $entity->id(),
        'coh_clean_page' => 'true',
      ];
      $params += $query_param;
      $event->addUrl('sitestudio-page-builder.layout_canvas.build', [
        'url' => Url::fromRoute("entity.{$entity->getEntityTypeId()}.sitestudio_build", $params)->toString(),
        'method' => 'POST',
      ]);
    }

    // Builder save page
    $event->addUrl('frontend-builder-save', [
      'url' => Url::fromRoute('sitestudio-page-builder.save')->toString(),
      'method' => 'POST',
    ]);

    // Get the react app library asset
    $lib = $this->libraryDiscovery->getLibraryByName('sitestudio_page_builder', 'cohesion-frontend-edit-scripts');

    if(isset($lib['js'][0]['data'])) {
      $event->addUrl('frontend-builder-js', [
        'url' => $this->fileUrlGenerator->generateString($lib['js'][0]['data']),
        'method' => 'GET',
      ]);
    }

    // Get the react app library asset
    $lib = $this->libraryDiscovery->getLibraryByName('cohesion', 'global_libraries.visual_page_builder_element_js_loader');
    if (isset($lib['js'][0]['data'])) {
      $urls = [];
      foreach ($lib['js'] as $js) {
        if (isset($js['data'])) {
          $urls[] = [
            'url' => $this->fileUrlGenerator->generateString($js['data']),
            'method' => 'GET',
          ];
        }
      }

      $event->addUrl('visual-page-builder-element-js-loader', $urls);
    }
  }

  /**
   * Alter the build array for the CohesionLayoutViewBuilderEvent view
   * Add to drupal settings all attributes for the page builder
   *
   * @param \Drupal\cohesion_elements\Event\CohesionLayoutViewBuilderEvent $event
   *
   * * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   *   Thrown if the entity type doesn't exist.
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   *   Thrown if the storage handler couldn't be loaded.
   */
  public function alterViewBuilder(CohesionLayoutViewBuilderEvent $event) {
    $build = $event->getBuild();
    $entity = $event->getEntity();
    $host = $entity->getParentEntity();
    $route_entity = $this->pageBuilderManager->shouldEnablePageBuilder();

    if ($route_entity && $host && $host->getEntityTypeId() == $route_entity->getEntityTypeId() && $host->id() == $route_entity->id() && $host->access('update')) {
      // Get the latest layout canvas and pass it to drupalSettings
      $latest_entity = $this->entityRepository->getActive($entity->getEntityTypeId(), $entity->id());

      $json_values = json_decode($latest_entity->getJsonValues());
      if ($payload = $this->cohesionUtils->getPayloadForLayoutCanvasDataMerge($latest_entity)) {
        $response = $this->cohesionApiClient->layoutCanvasDataMerge($payload);
        if ($response && $response['code'] == 200) {
          $json_values = $response['data']->layoutCanvas;
          $element['#attached']['drupalSettings']['cohesion']['deletedComponents'] = $response['data']->deletedComponents;
        }
        else {
          throw new \Exception('Unable to parse layout canvas: ' . $response['data']['error']);
        }
      }
      $build['#attached']['drupalSettings']['cohesion']['cohCanvases']['cohcanvas-' . $entity->id()] = $json_values;

      // Get the latest host and pass the next states it can transition into to
      // drupalSettings.
      $latest_host = $this->entityRepository->getActive($host->getEntityTypeId(), $host->id());
      if($latest_host->getRevisionId() != $host->getRevisionId()) {
        $build['#attached']['drupalSettings']['cohesion']['isLatest'] = FALSE;
      }

      $transition_labels = [];
      $moderation_information = NULL;
      if ($this->moduleHandler->moduleExists('content_moderation')) {
        /** @var \Drupal\content_moderation\ModerationInformationInterface $moderation_information */
        $moderation_information = \Drupal::service('content_moderation.moderation_information');
      }

      if ($moderation_information && $moderation_information->isModeratedEntity($latest_host)) {
        /** @var \Drupal\content_moderation\StateTransitionValidationInterface $validator */
        $validator = \Drupal::service('content_moderation.state_transition_validation');
        // Get the states the entity can transition into and the current state
        $default = $moderation_information->getOriginalState($latest_host);
        $build['#attached']['drupalSettings']['cohesion']['currentState']['label'] = $default->label();
        $build['#attached']['drupalSettings']['cohesion']['currentState']['state'] = $default->id();
        $transitions = $validator->getValidTransitions($latest_host, $this->user);

        foreach ($transitions as $transition) {
          $transition_to_state = $transition->to();
          $transition_labels[$transition_to_state->id()] = [
            'state' => $transition_to_state->id(),
            'label' => $transition_to_state->label(),
          ];

          if ($default->id() === $transition_to_state->id()) {
            $transition_labels[$transition_to_state->id()]['selected'] = TRUE;
          }
        }
      } else {
        // If not workflow enabled add published/unpublished state
        $transition_labels['published'] = [
          'state' => 'published',
          'label' => $this->t('Published'),
        ];
        $transition_labels['unpublished'] = [
          'state' => 'unpublished',
          'label' => $this->t('Unpublished'),
        ];

        if (!method_exists($latest_host, 'isPublished') || $latest_host->isPublished()) {
          $transition_labels['published']['selected'] = TRUE;
          $build['#attached']['drupalSettings']['cohesion']['currentState']['label'] = $this->t('Published');
          $build['#attached']['drupalSettings']['cohesion']['currentState']['state'] = 'published';
        } else {
          $transition_labels['unpublished']['selected'] = TRUE;
          $build['#attached']['drupalSettings']['cohesion']['currentState']['label'] = $this->t('Unpublished');
          $build['#attached']['drupalSettings']['cohesion']['currentState']['state'] = 'unpublished';
        }
      }

      $build['#attached']['drupalSettings']['cohesion']['moderationStates'] = array_values($transition_labels);
    }

    $event->setBuild($build);
  }

}
