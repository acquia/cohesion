<?php

namespace Drupal\example_custom_select\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for example custom select endpoint.
 */
class ExampleCustomSelectController extends ControllerBase {

  /**
   * Return a custom JsonResponse for select options.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *
   */
  public function index(Request $request) {
    return new JsonResponse([
      [
        'label' => 'Option 1',
        'value' => 'option1',
        'group' => 'Group A',
      ],
      [
        'label' => 'Option 2',
        'value' => 'option2',
        'group' => 'Group B',
      ],
    ]);
  }

}
