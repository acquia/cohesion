<?php

namespace Drupal\cohesion\Controller;

use Drupal\Component\Render\FormattableMarkup;
use Drupal\Core\Entity\Controller\EntityController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * Class CohesionEntityController.
 *
 * Returns responses for cohesion entities routes.
 *
 * @package Drupal\cohesion\Controller
 */
class CohesionEntityController extends EntityController {

  /**
   * Displays add links for the available bundles.
   * Redirects to the add form if there's only one bundle available.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   * @param $entity_type_id
   *
   * @return array
   */
  public function inUse(RouteMatchInterface $route_match, $entity_type_id) {

    $entity = $route_match->getParameter($entity_type_id);

    $list = $entity->getInUseMessage();

    $rows = function ($result = []) {
      $rows_data = [];
      foreach ($result as $entity) {
        $rows_data[] = [
          [
            'data' => new FormattableMarkup('<a href=":link">@name</a>', [
              ':link' => $entity['url'],
              '@name' => $entity['name'],
            ]),
          ],
        ];
      }
      return $rows_data;
    };

    $in_use_entities = \Drupal::service('cohesion_usage.update_manager')->getFormattedInUseEntitiesList($entity);

    foreach ($in_use_entities as $type => $result) {
      $list[] = [
        '#type' => 'details',
        '#open' => TRUE,
        '#title' => $type,
        'table' => [
          '#type' => 'table',
          '#header' => [],
          '#rows' => $rows($result),
        ],
      ];
    }

    return $list;
  }

  /**
   * Provides a generic title callback for a in use entity.
   *
   * @param \Drupal\Core\Routing\RouteMatchInterface $route_match
   *   The route match.
   * @param \Drupal\Core\Entity\EntityInterface $_entity
   *   (optional) An entity, passed in directly from the request attributes.
   *
   * @return string|null
   *   The title for the entity in use page, if an entity was found.
   */
  public function inUseTitle(RouteMatchInterface $route_match, ?EntityInterface $_entity = NULL) {
    if ($entity = $this->doGetEntity($route_match, $_entity)) {
      return $this->t('In use: %entity', ['%entity' => $entity->label()]);
    }
  }

}
