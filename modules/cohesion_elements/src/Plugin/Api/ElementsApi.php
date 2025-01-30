<?php

namespace Drupal\cohesion_elements\Plugin\Api;

use Drupal\cohesion\ApiPluginBase;

/**
 * Elements Api plugin.
 *
 * @package Drupal\cohesion_elements
 *
 * @Api(
 *   id = "elements_api",
 *   name = @Translation("Default elements build from API"),
 * )
 */
class ElementsApi extends ApiPluginBase {

  /**
   *
   */
  public function getForms() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function callApi() {
    $this->response = \Drupal::service('cohesion.api_client')->buildElements($this->data);
  }

}
