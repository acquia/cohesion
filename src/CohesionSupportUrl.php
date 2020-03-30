<?php

namespace Drupal\cohesion;

/**
 * Provides version prefix for help keys.
 */
class CohesionSupportUrl {

  /**
   * {@inheritdoc}
   */
  public function getSupportUrlPrefix() {

    // Get the support website url.
    $support_url = \Drupal::keyValue('cohesion.assets.static_assets')->get('support_url');

    // Get the module info.
    $module_info = system_get_info('module', 'cohesion');

    // Get the module version.
    $version = $module_info['version'];
    // Remove '8.x-'.
    $version = str_replace('8.x-', '', $version);
    // Remove '-master'.
    if (strstr($module_info['version'], '-master')) {
      $version = str_replace('-master', '', $version);
    }
    $versions = explode('.', $version);
    return $support_url['url'] . $versions[0] . '.' . $versions[1] . '/';

  }

}
