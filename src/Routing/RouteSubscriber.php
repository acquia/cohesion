<?php

namespace Drupal\cohesion\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Symfony\Component\Routing\RouteCollection;

/**
 *
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * Disable access to cohesion entities if assets have not been imported
   * or "Use Site studio" option is set to 'disable'
   * {@inheritdoc}.
   */
  protected function alterRoutes(RouteCollection $collection) {
    $config = \Drupal::configFactory()->getEditable('cohesion.settings');

    $allowed_routes = [
      'cohesion.settings',
      'cohesion.configuration',
      'cohesion.configuration.account_settings',
      'cohesion.configuration.batch',
      'cohesion_website_settings.elements',
    ];

    foreach ($collection->all() as $key => $route) {
      if (!$config->get('asset_is_imported') || !(\Drupal::service('cohesion.utils')->usedx8Status())) {
        if (!in_array($key, $allowed_routes) && (strpos($key, 'cohesion') !== FALSE)) {
          $route->setRequirement('_access', 'FALSE');
        }
      }
    }
  }

}
