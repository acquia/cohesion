<?php

namespace Drupal\sitestudio_page_builder\Services;

use Drupal\cohesion\Routing\CohesionRouteMatchInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\field\Entity\FieldConfig;
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
   *
   * @param \Drupal\cohesion\Routing\CohesionRouteMatchInterface $current_route_match
   * Current rout match service.
   */
  public function __construct(CohesionRouteMatchInterface $current_route_match) {
    $this->currentRouteMatch = $current_route_match;
  }

  public static function create(ContainerInterface $container) {
    return new self(
      $container->get('cohesion_current_route_match')
    );
  }

  /**
   * Should the Visual page builder be enabled?
   *
   * {@inheritDoc}
   */
  public function shouldEnablePageBuilder() {
    $entities = $this->currentRouteMatch->getRouteEntities();

    // Only routes containing one entity can match
    if (count($entities) === 1) {
      $entity = reset($entities);

      $allowed_routes = [
        "entity.{$entity->getEntityTypeId()}.canonical",
        "entity.{$entity->getEntityTypeId()}.latest_version",
      ];

      if ($entity instanceof ContentEntityInterface && in_array($this->currentRouteMatch->getRouteName(), $allowed_routes)) {
        // Check that there is a Layout canvas field on the entity.
        foreach ($entity->getFieldDefinitions() as $field) {
          if ($field instanceof FieldConfig && $field->getType() == 'cohesion_entity_reference_revisions') {
            return $entity;
          }
        }
      }
    }

    return FALSE;
  }

}
