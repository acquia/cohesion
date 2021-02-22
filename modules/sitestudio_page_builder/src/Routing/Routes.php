<?php

namespace Drupal\sitestudio_page_builder\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Routing\RouteProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides routes for building the frontend builder rendering for each entity type
 */
class Routes implements ContainerInjectionInterface {

  /**
   * The entity type manager service
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */

  protected $entityTypeManager;

  /**
   * The route provider service
   *
   * @var \Drupal\Core\Routing\RouteProviderInterface
   */
  protected $routeProvider;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, RouteProviderInterface $route_provider) {
    $this->entityTypeManager = $entity_type_manager;
    $this->routeProvider = $route_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container){
    return new static(
      $container->get('entity_type.manager'),
      $container->get('router.route_provider')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\Route[]
   *   An array of route objects.
   */
  public function routes() {
    // For each entity type that has a canonical url, duplicate this url, prefix it with /cohesionapi
    // and define its layout canvas build route
    // This way the entity is passed to the build function context which might be needed from other modules
    // to render page. For example views that rely on the node id to display results
    $routes = [];
    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types as $entity_type) {
      if ($entity_type instanceof ContentEntityType && $entity_type->hasLinkTemplate('canonical')) {
        $route = $this->routeProvider->getRouteByName("entity.{$entity_type->id()}.canonical");
        $route->setPath('/cohesionapi/page-builder/build' . $route->getPath());
        $route->setMethods(['POST']);
        $route->setRequirement('_user_is_logged_in', "TRUE");
        $route->setRequirement('_permission', "access components");
        $route->setOption('no_cache', 'TRUE');
        $route->setOption('sitestudio_build', 'TRUE'); // Tag the route
        $route->setDefault('_controller', '\Drupal\sitestudio_page_builder\Controller\SitestudioPageBuilderController::buildLayoutCanvas');
        $routes["entity.{$entity_type->id()}.sitestudio_build"] = $route;
      }
    }

    return $routes;
  }

}