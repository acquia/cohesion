<?php

namespace Drupal\cohesion_custom_styles;

use Drupal\cohesion\CohesionHtmlRouteProvider;
use Drupal\cohesion_custom_styles\Controller\CustomStylesController;
use Drupal\Core\Entity\EntityTypeInterface;

/**
 * Class CustomStylesHtmlRouteProvider.
 *
 * Provides routes for Site Studio base styles entities.
 *
 * @package Drupal\cohesion_custom_styles
 */
class CustomStylesHtmlRouteProvider extends CohesionHtmlRouteProvider {

  /**
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *
   * @return null|\Symfony\Component\Routing\Route
   */
  protected function getAddPageRoute(EntityTypeInterface $entity_type) {
    if ($route = parent::getAddPageRoute($entity_type)) {
      $route->setDefault('_controller', CustomStylesController::class . '::addPage');
      $route->setDefault('_title_callback', CustomStylesController::class . '::addTitle');

      return $route;
    }
  }

}
