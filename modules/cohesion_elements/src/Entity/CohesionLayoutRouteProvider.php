<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\Routing\EntityRouteProviderInterface;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Provides the edit route for cohesion_layout entities in the SettingsTray.
 *
 * @package Drupal\cohesion_elements\Entity
 */
class CohesionLayoutRouteProvider implements EntityRouteProviderInterface {

  /**
   * {@inheritdoc}
   */
  public function getRoutes(EntityTypeInterface $entity_type) {
    $route_collection = new RouteCollection();

    $route = (new Route('/admin/cohesion/components/cohesion_layout/{cohesion_layout}/{component_instance_uuid}/{component_id}/edit'))->setDefault('_title', 'Edit cohesion layout')
    // ->setRequirement('cohesion_layout', '\d+')
      ->setDefault('_entity_form', 'cohesion_layout.edit')
      ->setRequirement('_entity_access', 'cohesion_layout.edit');

    $route_collection->add('entity.cohesion_layout.edit_form', $route);

    return $route_collection;
  }

}
