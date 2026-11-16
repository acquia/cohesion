<?php

namespace Drupal\cohesion;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rest\Plugin\ResourceBase;
use Drupal\rest\Plugin\ResourceInterface;

/**
 * Site Studio base class for resource plugins.
 *
 * @see \Drupal\rest\Annotation\RestResource
 * @see \Drupal\rest\Plugin\Type\ResourcePluginManager
 * @see \Drupal\rest\Plugin\ResourceInterface
 * @see plugin_api
 */
abstract class CohesionResourceBase extends ResourceBase implements ContainerFactoryPluginInterface, ResourceInterface {

  /**
   * {@inheritDoc}
   */
  public function permissions() {
    // We don't need to auto generated permissions.
    return [];
  }

  /**
   * {@inheritdoc}
   */
  protected function getBaseRouteRequirements($method) {

    $requirements = [
      '_access' => 'TRUE',
    ];

    // By default DX8 endpoint only needs user to be logged in.
    if ($method === 'GET') {
      $requirements['_user_is_logged_in'] = 'TRUE';
    }
    else {
      // Plugins extending this should override/extend this method.
      $requirements['_access'] = 'FALSE';
    }

    return $requirements;
  }

}
