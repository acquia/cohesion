<?php

namespace Drupal\cohesion\Routing;

/**
 * Provide an interface for extending route match
 *
 * @package Drupal\cohesion\Routing
 */
interface CohesionRouteMatchInterface {

  /**
   * Get all entities from the current route
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   */
  public function getRouteEntities();

}
