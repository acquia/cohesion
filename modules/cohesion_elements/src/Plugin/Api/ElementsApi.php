<?php

namespace Drupal\cohesion_elements\Plugin\Api;

use Drupal\cohesion\CohesionApiClient;
use Drupal\cohesion\ApiPluginBase;

/**
 * Class ElementsApi.
 *
 * @package Drupal\cohesion_elements
 *
 * @Api(
 *   id = "elements_api",
 *   name = @Translation("Default elements build from API"),
 * )
 */
class ElementsApi extends ApiPluginBase {

  public function getForms() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function callApi() {
    $this->response = CohesionApiClient::buildElements($this->data);
  }

}
