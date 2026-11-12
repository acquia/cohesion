<?php

namespace Drupal\cohesion\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Controller\ControllerBase;
use GuzzleHttp\Exception\ClientException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class ValidatePMCEndpoint.
 *
 * Makes a request to the API to validate a pseudo, modifier or class.
 *
 * @package Drupal\cohesion\Controller
 */
class ValidatePMCEndpoint extends ControllerBase {

  /**
   *
   */
  public function index(Request $request) {

    try {
      $results = JSON::decode($request->getContent());

      $response = \Drupal::service('cohesion.api_client')->valiatePMC($results);

      if ($response && $response['code'] == 200) {
        $status = $response['code'];
        $result = $response['data'];
      }
      else {
        $status = 500;
        $result = [
          'error' => t('Unknown error'),
        ];
      }

    }
    catch (ClientException $e) {
      $status = 500;
      $result = [
        'error' => t('Connection error'),
      ];
    }

    return new CohesionJsonResponse($result, $status);
  }

}
