<?php

namespace Drupal\sitestudio_page_builder\EventSubscriber;

use Drupal\cohesion_elements\Event\CohesionLayoutViewBuilderEvent;
use Drupal\cohesion\Event\FrontendUrlsEvent;
use Drupal\content_moderation\ModerationInformation;
use Drupal\content_moderation\StateTransitionValidationInterface;
use Drupal\Core\Asset\LibraryDiscoveryInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The moderation information service.
   *
   * @var \Drupal\content_moderation\ModerationInformation
   */
  protected $moderationInformation;

  /**
   * Moderation state transition validation service.
   *
   * @var \Drupal\content_moderation\StateTransitionValidationInterface
   */
  protected $validator;


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
   * SitestudioPageBuilderEventSubscriber constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *  The entity type manager.
   * @param \Drupal\Core\Session\AccountInterface $user
   *  The current user account.
   * @param \Drupal\content_moderation\ModerationInformation $moderation_information
   *  The moderation information service.
   * @param \Drupal\content_moderation\StateTransitionValidationInterface $validator
   *  Moderation state transition validation service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   * Current rout match service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *  Current route match service.
   * @param \Drupal\Core\Asset\LibraryDiscoveryInterface $library_discovery
   *  Library discovery service
   * @param \Drupal\sitestudio_page_builder\Services\SitestudioPageBuilderManagerInterface $page_builder_manager
   *  The page builder manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountInterface $user, ModerationInformation $moderation_information,
                              StateTransitionValidationInterface $validator, RouteMatchInterface $current_route_match, RequestStack $request_stack,
                              LibraryDiscoveryInterface $library_discovery, SitestudioPageBuilderManagerInterface $page_builder_manager ) {
    $this->entityTypeManager = $entity_type_manager;
    $this->user = $user;
    $this->moderationInformation = $moderation_information;
    $this->validator = $validator;
    $this->currentRouteMatch = $current_route_match;
    $this->currentRequest = $request_stack->getCurrentRequest();
    $this->libraryDiscovery = $library_discovery;
    $this->pageBuilderManager = $page_builder_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      // Static class constant => method on this class.
      FrontendUrlsEvent::EVENT_NAME => 'registerUrls',
      CohesionLayoutViewBuilderEvent::ALTER => 'alterViewBuilder',
    ];
  }

  /**
   * Registers urls for frontend builder to work
   *
   * @param \Drupal\cohesion\Event\FrontendUrlsEvent $event
   */
  public function registerUrls(FrontendUrlsEvent $event) {
    // Each build method has its own url derivating from the entity canonical url
    // @see \Drupal\sitestudio_page_builder\Routing\Route
    $query_param = $this->currentRequest->query->all();

    if($entity = $this->pageBuilderManager->shouldEnablePageBuilder()) {
      $raw_params = $this->currentRouteMatch->getRawParameters()->all();
      $raw_params['coh_clean_page'] = 'true';
      $raw_params += $query_param;
      $event->addFrontEndUrl('sitestudio-page-builder.layout_canvas.build', [
        'url' => Url::fromRoute("entity.{$entity->getEntityTypeId()}.sitestudio_build", $raw_params  )->toString(),
        'method' => 'POST'
      ]);
    }

    // Builder save page
    $event->addFrontEndUrl('frontend-builder-save', [
      'url' => Url::fromRoute('sitestudio-page-builder.save')->toString(),
      'method' => 'POST'
    ]);

    // Get the react app library asset
    $lib = $this->libraryDiscovery->getLibraryByName('sitestudio_page_builder','cohesion-frontend-edit-scripts');
    if(isset($lib['js'][0]['data'])) {
      $event->addFrontEndUrl('frontend-builder-js', [
        'url' => file_url_transform_relative(file_create_url($lib['js'][0]['data'])),
        'method' => 'GET'
      ]);
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
  public function alterViewBuilder(CohesionLayoutViewBuilderEvent $event){
    $build = $event->getBuild();
    $entity = $event->getEntity();
    $host = $entity->getParentEntity();
    $route_entity = $this->pageBuilderManager->shouldEnablePageBuilder();

    if($route_entity && $host && $host->getEntityTypeId() == $route_entity->getEntityTypeId() && $host->id() == $route_entity->id() && $host->access('edit')) {
      // Get the latest layout canvas and pass it to drupalSettings
      $latest_entity = $entity;
      if(!$entity->isLatestRevision()) {
        /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
        $storage = $this->entityTypeManager->getStorage($entity->getEntityTypeId());
        $latest_revision_id = $storage->getLatestTranslationAffectedRevisionId($entity->id(), $entity->language()->getId());
        $latest_entity = $storage->loadRevision($latest_revision_id);
        $latest_entity = $latest_entity->getTranslation($entity->language()->getId());
      }
      $build['#attached']['drupalSettings']['cohesion']['cohCanvases']['cohcanvas-' . $entity->id()] = json_decode($latest_entity->getJsonValues());

      // Get the latest host and pass the next states it can transition into to drupalSettings
      $latest_host = $host;
      if(!$host->isLatestRevision()) {
        /** @var \Drupal\Core\Entity\ContentEntityStorageInterface $storage */
        $storage = $this->entityTypeManager->getStorage($host->getEntityTypeId());
        $latest_revision_id = $storage->getLatestTranslationAffectedRevisionId($host->id(), $host->language()->getId());
        $latest_host = $storage->loadRevision($latest_revision_id);
        $latest_host = $latest_host->getTranslation($host->language()->getId());
        $build['#attached']['drupalSettings']['cohesion']['isLatest'] = FALSE;
      }

      $transition_labels = [];
      if($this->moderationInformation->isModeratedEntity($latest_host)) {
        // Get the states the entity can transition into and the current state
        $default = $this->moderationInformation->getOriginalState($latest_host);
        $build['#attached']['drupalSettings']['cohesion']['currentState'] = $default->label();
        $transitions = $this->validator->getValidTransitions($latest_host, $this->user);

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
      }else {
        // If not workflow enabled add pusblished/unbublished state
        $transition_labels['published'] = [
          'state' => 'published',
          'label' => $this->t('Published'),
        ];
        $transition_labels['unpublished'] = [
          'state' => 'unpublished',
          'label' => $this->t('Unpublished'),
        ];

        if($latest_host->isPublished()) {
          $transition_labels['published']['selected'] = TRUE;
          $build['#attached']['drupalSettings']['cohesion']['currentState'] = $this->t('Published');
        }else {
          $transition_labels['unpublished']['selected'] = TRUE;
          $build['#attached']['drupalSettings']['cohesion']['currentState'] = $this->t('Unpublished');
        }
      }

      $build['#attached']['drupalSettings']['cohesion']['moderationStates'] = array_values($transition_labels);
    }


    $event->setBuild($build);
  }

}