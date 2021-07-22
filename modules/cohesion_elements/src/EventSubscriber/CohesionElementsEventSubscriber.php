<?php

namespace Drupal\cohesion_elements\EventSubscriber;

use Drupal\cohesion_elements\ComponentContentInterface;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion\Event\FrontendUrlsEvent;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for the cohesion element module, registering frontend urls
 *
 */
class CohesionElementsEventSubscriber implements EventSubscriberInterface {

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
   * The entity field manager
   *
   * @var \Drupal\Core\Entity\EntityFieldManagerInterface
   */

  protected $entityFieldManager;

  /**
   * SitestudioPageBuilderEventSubscriber constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *  The current user account.
   * @param \Drupal\Core\Routing\RouteMatchInterface $current_route_match
   * Current rout match service.
   * @param \Drupal\Core\Entity\EntityFieldManagerInterface $entity_field_manager
   * The entity field manager
   */
  public function __construct(AccountInterface $user, RouteMatchInterface $current_route_match, EntityFieldManagerInterface $entity_field_manager) {
    $this->user = $user;
    $this->currentRouteMatch = $current_route_match;
    $this->entityFieldManager = $entity_field_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FrontendUrlsEvent::EVENT_NAME => 'registerUrls',
    ];
  }

  /**
   * Registers urls for component, helper sidebar browser list
   *
   * @param \Drupal\cohesion\Event\FrontendUrlsEvent $event
   */
  public function registerUrls(FrontendUrlsEvent $event) {
    $this->addComponentListUrl($event);
    $this->addHelperListUrl($event);
    $this->addComponentContentListUrl($event);
  }

  /**
   * Add components list url for the sidebar browser to the front and admin urls
   *
   * @param $event
   */
  private function addComponentListUrl($event) {
    $route_params = [
      'entity_type' => 'cohesion_component'
    ];

    $this->functionGetAccessList($route_params);

    $url = Url::fromRoute('drupal_data_endpoint.element_group_info', [$route_params])->toString();

    $event->addFrontEndUrl('component-elements-list', [
      'url' => $url,
      'method' => 'GET'
    ]);

    $event->addAdminUrls('component-elements-list', [
      'url' => $url,
      'method' => 'GET'
    ]);
  }

  /**
   * Add the helpers list for the sidebar browser to the front and admin urls
   *
   * @param $event
   */
  private function addHelperListUrl($event) {
    $route_params = [
      'entity_type' => 'cohesion_helper'
    ];

    $this->functionGetAccessList($route_params);

    // Access elements on the frontend is not allowed
    $route_params['access_elements'] = 'false';
    $url = Url::fromRoute('drupal_data_endpoint.element_group_info', [$route_params])->toString();
    $event->addFrontEndUrl('helpers-elements-list', [
      'url' => $url,
      'method' => 'GET'
    ]);
  }

  /**
   * Add component content list for the sidebar browser to the front and adming urls
   *
   * @param $event
   */
  private function addComponentContentListUrl($event) {
    $route_params = [];
    $entity = $this->getCurrentPathEntity();

    if($entity instanceof ComponentContentInterface && $this->currentRouteMatch->getRouteName()) {
      $route_params['componentPath'] = Url::fromRouteMatch($this->currentRouteMatch)->getInternalPath();
    }

    if(!empty($route_params)) {
      $route_params = [$route_params];
    }

    $url = Url::fromRoute('cohesion_elements.endpoints.component_contents', $route_params)->toString();
    $event->addFrontEndUrl('component-content-elements-list', [
      'url' => $url,
      'method' => 'GET'
    ]);

    $event->addAdminUrls('component-content-elements-list', [
      'url' => $url,
      'method' => 'GET'
    ]);
  }

  /**
   * Return the current request entity
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  private function getCurrentPathEntity() {
    foreach ($this->currentRouteMatch->getParameters() as $param_key => $param) {
      if ($param instanceof ContentEntityInterface) {
        return $param;
      }
    }
    return NULL;
  }

  /**
   * Build the route params for entity_type_access and bundle_access
   *
   * @param $route_params
   */
  private function functionGetAccessList(&$route_params) {
    $entity = $this->getCurrentPathEntity();

    if($entity instanceof Component && $this->currentRouteMatch->getRouteName()) {
      $route_params['componentPath'] = Url::fromRouteMatch($this->currentRouteMatch)->getInternalPath();
    }

    if($entity instanceof ContentEntityInterface) {
      $route_params['entity_type_access'] = $entity->getEntityTypeId();
      $route_params['bundle_access'] = $entity->bundle();
    }

    // Global settings for these pages
    $allowed_pages = [
      'entity.cohesion_master_templates.edit_form',
      'entity.cohesion_content_templates.edit_form',
      'entity.cohesion_view_templates.edit_form',
      'entity.cohesion_menu_templates.edit_form',
    ];

    if (in_array($this->currentRouteMatch->getRouteName(), $allowed_pages)) {
      $route_params['entity_type_access'] = 'dx8_templates';
      $route_params['bundle_access'] = 'dx8_templates';
    }
  }

}
