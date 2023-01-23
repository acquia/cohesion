<?php

namespace Drupal\cohesion_style_helpers;

use Drupal\cohesion\CohesionHtmlRouteProvider;
use Drupal\cohesion_style_helpers\Controller\StyleHelpersController;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Provides routes for Site Studio base styles entities.
 *
 * @see Drupal\Core\Entity\Routing\AdminHtmlRouteProvider
 * @see Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider
 */
class StyleHelpersHtmlRouteProvider extends CohesionHtmlRouteProvider {

  /**
   *
   */
  protected function getAddPageRoute(EntityTypeInterface $entity_type) {
    if ($route = parent::getAddPageRoute($entity_type)) {
      $route->setDefault('_controller', StyleHelpersController::class . '::addPage');
      $route->setDefault('_title_callback', StyleHelpersController::class . '::addTitle');

      return $route;
    }
  }

}
