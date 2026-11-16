<?php

namespace Drupal\cohesion_elements;

/**
 * Defines the interface for services which discover front-end components.
 */
interface CustomComponentDiscoveryInterface {

  /**
   * Find all available front-end components.
   *
   * @return \Drupal\Core\Extension\Extension[]
   *   The discovered components.
   */
  public function getComponents();

}
