<?php

namespace Drupal\sitestudio_page_builder\Routing;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityType;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides routes for building the frontend builder rendering for each entity
 * type
 */
class Routes implements ContainerInjectionInterface {

  /**
   * The entity type manager service
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns an array of route objects.
   *
   * @return \Symfony\Component\Routing\RouteCollection
   *   An array of route objects.
   */
  public function routes() {
    // For each entity type that has a canonical url, duplicate this url, prefix
    // it with /cohesionapi and define its layout canvas build route. This way
    // the entity is passed to the build function context which might be needed
    // from other modules to render page. For example views that rely on the
    // node id to display results
    $routes = new RouteCollection();
    $entity_types = $this->entityTypeManager->getDefinitions();
    foreach ($entity_types as $entity_type) {
      if ($entity_type instanceof ContentEntityType && $entity_type->hasLinkTemplate('canonical')) {
        $route = new Route("/cohesionapi/page-builder/build/{$entity_type->id()}/{{$entity_type->id()}}");
        $route->setMethods(['POST']);
        $route->setRequirement('_user_is_logged_in', "TRUE");
        $route->setRequirement('_permission', "access visual page builder");
        $route->setOption('no_cache', 'TRUE');
        // Tag the route
        $route->setOption('sitestudio_build', 'TRUE');
        $route->setDefault('_controller', '\Drupal\sitestudio_page_builder\Controller\SitestudioPageBuilderController::buildLayoutCanvas');
        $route->setOption('parameters', [$entity_type->id() => ['type' => 'entity:' . $entity_type->id()]]);
        $routes->add("entity.{$entity_type->id()}.sitestudio_build", $route);
      }
    }

    return $routes;
  }

}
