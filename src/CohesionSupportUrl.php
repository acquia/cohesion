<?php

namespace Drupal\cohesion;

/**
 * Provides version prefix for help keys.
 */
class CohesionSupportUrl {

  const SUPPORT_URL = 'https://docs.acquia.com/redirect/';

  /**
   * {@inheritdoc}
   */
  public function getSupportUrlPrefix() {

    // Get the module info.
    $module_info = [];
    try {
      $module_info = \Drupal::service('extension.list.module')->getExtensionInfo('cohesion');
    }
    catch (\Throwable $e) {
    }

    // Get the module version.
    $version = $module_info['version'];
    // Remove '8.x-'.
    $version = str_replace('8.x-', '', $version);
    // Remove '-master'.
    if (strstr($module_info['version'], '-master')) {
      $version = str_replace('-master', '', $version);
    }
    $versions = explode('.', $version);
    return self::SUPPORT_URL . $versions[0] . '.' . $versions[1] . '/';

  }

}
