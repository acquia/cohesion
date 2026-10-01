<?php

namespace Drupal\cohesion\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ParseJsonEndpoint.
 *
 * Makes a request to the API to parse data.
 *
 * @package Drupal\cohesion\Controller
 */
class ParseJsonEndpoint extends ControllerBase {

  /**
   *
   */
  public function index(Request $request) {
    try {
      $body = $request->getContent();
      $results = json_decode($body);

      $response = \Drupal::service('cohesion.api_client')->parseJson($request->attributes->get('command'), $results);

      if ($response && $response['code'] == 200) {
        $status = $response['code'];
        $result = $response['data'];
      }
      else {
        $status = 500;
        $result = [
          'error' => $this->t('Unknown error.'),
        ];
      }

    }
    catch (ClientException $e) {
      $status = 500;
      $result = [
        'error' => $this->t('Connection error.'),
      ];
    }

    return new CohesionJsonResponse($result, $status);
  }

}
