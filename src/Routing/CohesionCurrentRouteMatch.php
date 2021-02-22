<?php

namespace Drupal\cohesion\Routing;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Routing\CurrentRouteMatch;

class CohesionCurrentRouteMatch extends CurrentRouteMatch implements CohesionRouteMatchInterface {

  /**
   * {@inheritdoc}
   */
  public function getRouteEntities() {
    $entities = [];
    foreach ($this->getParameters() as $param_key => $param) {
      if($param instanceof EntityInterface) {
        $entities[] = $param;
      }
    }

    return $entities;
  }

}

