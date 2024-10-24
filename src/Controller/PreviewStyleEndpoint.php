<?php

namespace Drupal\cohesion\Controller;

use Drupal\Component\Render\MarkupInterface;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Component\Serialization\Json;

/**
 * Class PreviewStyleEndpoint.
 *
 * Makes a request to the API to create a stylesheet for the element preview.
 *
 * @package Drupal\cohesion\Controller
 */
class PreviewStyleEndpoint extends ControllerBase {

  /**
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function index(Request $request) {

    $entity_type_id = $request->attributes->get('entity_type_id');

    // Build generic response data.
    $data = [];
    // Sanitize the style JSON form data sent from Angular.
    $req = Json::decode($request->getContent());

    $style_model = $req;
    $mapper = [];
    if (isset($style_model['mapper'])) {
      $mapper = $style_model['mapper'];
      unset($style_model['mapper']);
    }

    /** @var \Drupal\cohesion\Plugin\Api\PreviewApi $send_to_api */
    $send_to_api = \Drupal::service('plugin.manager.api.processor')->createInstance('preview_api');

    $send_to_api->setupPreview($entity_type_id, $style_model, $mapper);
    $success = $send_to_api->send();
    $response = $send_to_api->getData();

    $error = TRUE;
    $status = 400;
    if ($success) {
      if (is_array($response)) {
        if ($entity_type_id == 'cohesion_custom_style') {
          $data = $send_to_api->getResponseStyles('theme') ? $send_to_api->getResponseStyles('theme') : [];
        }
        else {
          $data = $send_to_api->getResponseStyles('base') ? $send_to_api->getResponseStyles('base') : [];
        }

        $data = \Drupal::service('twig')->renderInline($data);
        if ($data instanceof MarkupInterface) {
          $data = $data->__toString();
        }
      }
      $error = FALSE;
      $status = 200;
    }
    elseif (isset($response['error'])) {
      $data['error'] = $response['error'];
    }
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ], $status);
  }

}
