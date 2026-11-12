<?php

namespace Drupal\cohesion_elements\EventSubscriber;

use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
use Drupal\Core\Entity\Display\EntityFormDisplayInterface;
use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event subscriber for the cohesion element module, registering frontend urls.
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
   * The entity type id of the route entity
   * @var string|null
   */
  private $entity_type_id = NULL;

  /**
   * The bundle of the route entity
   * @var null|string
   */
  private $bundle = NULL;

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
    $this->setEntityTypeIdBundleFromParam();
  }

  /**
   * Retrieve the entity type id and bundle from the request params
   */
  private function setEntityTypeIdBundleFromParam() {
    $params = $this->currentRouteMatch->getParameters();
    $entity_type_id = NULL;
    $bundle = NULL;
    foreach ($this->currentRouteMatch->getParameters() as $param_key => $param) {
      if ($param instanceof EntityInterface) {
        $this->entity_type_id = $param->getEntityTypeId();
        $this->bundle = $param->bundle();
      }
      elseif ($param_key == 'entity_type_id') {
        $this->entity_type_id = $param;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CohesionJsAppUrlsEvent::FRONTEND_URL => 'registerFrontendUrls',
      CohesionJsAppUrlsEvent::ADMIN_URL => 'registerAdminUrls',
    ];
  }

  /**
   * Register urls for visual page builder.
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerFrontendUrls(CohesionJsAppUrlsEvent $event) {
    $this->registerUrls($event);
    $this->addHelperListUrl($event, FALSE);
  }

  /**
   * Register urls for admin app
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerAdminUrls(CohesionJsAppUrlsEvent $event) {
    $this->registerUrls($event);
    $this->addHelperListUrl($event, TRUE);

    $url = Url::fromRoute('cohesion_elements.component.preview_post')->toString();
    $event->addUrl('preview-layout-canvas', [
      'url' => $url,
      'method' => 'POST',
    ]);
  }

  /**
   * Registers urls for component, helper sidebar browser list
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerUrls(CohesionJsAppUrlsEvent $event) {
    $this->addComponentListUrl($event);
    $this->addComponentContentListUrl($event);
    $this->addElementsListUrl($event);
  }

  /**
   * Add components list url for the sidebar browser to the front and admin
   * urls.
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  private function addComponentListUrl(CohesionJsAppUrlsEvent $event) {
    $route_params = [
      'entity_type' => 'cohesion_component',
      'entity_type_access' => $this->entity_type_id,
      'bundle_access' => $this->bundle,
    ];
    $this->getAccessList($route_params, $event);

    $url = Url::fromRoute('drupal_data_endpoint.element_group_info', [$route_params])->toString();

    $event->addUrl('component-elements-list', [
      'url' => $url,
      'method' => 'GET',
    ]);
  }

  /**
   * Add the helpers list for the sidebar browser to the front and admin urls
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   * @param bool $for_admin
   *   whether the url is for admin pages or visual page builder
   */
  private function addHelperListUrl(CohesionJsAppUrlsEvent $event, $for_admin) {
    $route_params = [
      'entity_type' => 'cohesion_helper',
    ];
    $this->getAccessList($route_params, $event);

    $route_params['access_elements'] = 'false';
    // Only when in admin user can access helpers with elements
    // If visual page builder user cannot access any helper with elements.
    if ($form_state = $event->getFormState() && $for_admin) {
      $complete_form = $event->getFormState()->getCompleteForm();
      if (isset($complete_form['#attached']['drupalSettings']['cohesion']['permissions']) && in_array('access elements', $complete_form['#attached']['drupalSettings']['cohesion']['permissions'])) {
        $route_params['access_elements'] = 'true';
      }
    }

    // If the route is the custom component builder then we need to allow
    // element access.
    if ($this->currentRouteMatch->getRouteName() === 'cohesion_elements.custom_component.builder') {
      $route_params['custom_component_builder'] = 'true';
      $route_params['access_elements'] = 'true';
    }

    // Access elements on the frontend is not allowed.
    $url = Url::fromRoute('drupal_data_endpoint.element_group_info', [$route_params])->toString();
    $event->addUrl('helpers-elements-list', [
      'url' => $url,
      'method' => 'GET',
    ]);
  }

  /**
   * Add component content list for the sidebar browser to the front and
   * admin urls.
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  private function addComponentContentListUrl(CohesionJsAppUrlsEvent $event) {
    $route_params = [];

    if ($this->entity_type_id == 'component_content' && $this->currentRouteMatch->getRouteName()) {
      $route_params['componentPath'] = Url::fromRouteMatch($this->currentRouteMatch)
        ->getInternalPath();
    }

    if (!empty($route_params)) {
      $route_params = [$route_params];
    }

    $url = Url::fromRoute('cohesion_elements.endpoints.component_contents', $route_params)
      ->toString();
    $event->addUrl('component-content-elements-list', [
      'url' => $url,
      'method' => 'GET',
    ]);
  }

  /**
   * Build the route params for entity_type_access and bundle_access
   *
   * @param $route_params
   */
  private function getAccessList(&$route_params, CohesionJsAppUrlsEvent $event) {
    if ($this->entity_type_id == 'cohesion_component' && $this->currentRouteMatch->getRouteName()) {
      $route_params['componentPath'] = Url::fromRouteMatch($this->currentRouteMatch)->getInternalPath();
    }

    // If this is a content entity (has a form display) add the entity type and
    // bundle to the request to restrict component list if needed
    if ($form_state = $event->getFormState()) {
      $storage = $form_state->getStorage();
      if (isset($storage['form_display']) && $storage['form_display'] instanceof EntityFormDisplayInterface) {
        $route_params['entity_type_access'] = $storage['form_display']->getTargetEntityTypeId();
        $route_params['bundle_access'] = $storage['form_display']->getTargetBundle();
      }
    }

    // Global settings for these pages.
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

  /**
   * Add elements list for the sidebar browser to the front and admin urls.
   *
   * @param $event
   */
  private function addElementsListUrl(CohesionJsAppUrlsEvent $event) {
    $isCustom = FALSE;
    $entity_type_id = $this->entity_type_id;

    // If the route is the custom component builder then we need to
    // manually set the entity type.
    if ($this->currentRouteMatch->getRouteName() === 'cohesion_elements.custom_component.builder') {
      $entity_type_id = 'cohesion_component';
      $isCustom = TRUE;
    }

    $route_params = [
      'group' => 'elements',
      'withcategories' => TRUE,
      'entityTypeId' => $entity_type_id,
      'isCustom' => $isCustom,
    ];

    $url = Url::fromRoute('cohesion_website_settings.elements', $route_params)->toString();

    $event->addUrl('elements-list', [
      'url' => $url,
      'method' => 'GET',
    ]);
  }

}
