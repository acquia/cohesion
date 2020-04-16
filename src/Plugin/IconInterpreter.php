<?php

namespace Drupal\cohesion\Plugin;

use Drupal\cohesion\CohesionApiClient;
use Drupal\Component\Serialization\Json;

/**
 * Defines the IconInterpreter plugin.
 *
 * The IconInterpreter plugin actions calls that needs interpreting uploaded icon libraries.
 */
class IconInterpreter {

  /**
   * @param string $json
   *
   * @return array
   */
  public function sendToApi($json = '') {
    $results = new \stdClass();
    $results->body = Json::decode($json);
    return CohesionApiClient::resourceIcon($results);
  }

}
