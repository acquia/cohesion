<?php

namespace Drupal\sitestudio_page_builder\Services;

use Drupal\cohesion\Routing\CohesionRouteMatchInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service for visual page builder.
 *
 */
class SitestudioPageBuilderManager implements SitestudioPageBuilderManagerInterface, ContainerInjectionInterface {

  /**
   * Current route match service
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $currentRouteMatch;

  /**
   * SitestudioPageBuilderManager constructor.
   *    * @param \Drupal\cohesion\Routing\CohesionRouteMatchInterface $current_route_match
   * Current rout match service.
   */
  public function __construct(CohesionRouteMatchInterface $current_route_match) {
    $this->currentRouteMatch = $current_route_match;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('cohesion_current_route_match')
    );
  }

  /**
   * {@inheritDoc}
   */
  public function shouldEnablePageBuilder() {
    $entities = $this->currentRouteMatch->getRouteEntities();

    // Only routes containing one entity can match
    if(count($entities)) {
      $entity = reset($entities);

      $allowed_routes = [
        "entity.{$entity->getEntityTypeId()}.canonical",
        "entity.{$entity->getEntityTypeId()}.latest_version",
      ];

      if($entity instanceof ContentEntityInterface && in_array($this->currentRouteMatch->getRouteName(), $allowed_routes)) {
        return $entity;
      }
    }

    return FALSE;
  }

}
