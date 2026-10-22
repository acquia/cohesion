<?php

namespace Drupal\cohesion_templates;

use Drupal\cohesion\CohesionHtmlRouteProvider;
use Drupal\Core\Entity\EntityTypeInterface;
use Symfony\Component\Routing\Route;

/**
 * Provides routes for Site Studio base styles entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class CohesionContentTemplateHtmlRouteProvider extends CohesionHtmlRouteProvider {

  /**
   *
   */
  protected function getCollectionRoute(EntityTypeInterface $entity_type) {
    if ($entity_type->hasLinkTemplate('collection') && $entity_type->hasListBuilderClass() && ($admin_permission = $entity_type->getAdminPermission())) {

      $route = new Route($entity_type->getLinkTemplate('collection'));
      $route->addDefaults([
        '_entity_list' => $entity_type->id(),
        '_title_callback' => 'Drupal\cohesion_templates\Controller\CohesionTemplateSettingsController::title',
      ])->setRequirement('_permission', $admin_permission);

      return $route;
    }
  }

}
