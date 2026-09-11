<?php

namespace Drupal\example_custom_component_select\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Controller for example custom select endpoint.
 */
class ExampleCustomComponentSelectController extends ControllerBase {

  /**
   * Return a custom JsonResponse for select options.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *
   */
  public function index(Request $request) {
    return new JsonResponse([
      ['label' => 'Austria', 'value' => 'Austria'],
      ['label' => 'Belgium', 'value' => 'Belgium'],
      ['label' => 'Bulgaria', 'value' => 'Bulgaria'],
      ['label' => 'Croatia', 'value' => 'Croatia'],
      ['label' => 'Cyprus', 'value' => 'Cyprus'],
      ['label' => 'Czech Republic', 'value' => 'Czech Republic'],
      ['label' => 'Denmark', 'value' => 'Denmark'],
      ['label' => 'Estonia', 'value' => 'Estonia'],
      ['label' => 'Finland', 'value' => 'Finland'],
      ['label' => 'France', 'value' => 'France'],
      ['label' => 'Germany', 'value' => 'Germany'],
      ['label' => 'Greece', 'value' => 'Greece'],
      ['label' => 'Hungary', 'value' => 'Hungary'],
      ['label' => 'Ireland', 'value' => 'Ireland'],
      ['label' => 'Italy', 'value' => 'Italy'],
      ['label' => 'Latvia', 'value' => 'Latvia'],
      ['label' => 'Lithuania', 'value' => 'Lithuania'],
      ['label' => 'Luxembourg', 'value' => 'Luxembourg'],
      ['label' => 'Malta', 'value' => 'Malta'],
      ['label' => 'Netherlands', 'value' => 'Netherlands'],
      ['label' => 'Poland', 'value' => 'Poland'],
      ['label' => 'Portugal', 'value' => 'Portugal'],
      ['label' => 'Romania', 'value' => 'Romania'],
      ['label' => 'Slovakia', 'value' => 'Slovakia'],
      ['label' => 'Slovenia', 'value' => 'Slovenia'],
      ['label' => 'Spain', 'value' => 'Spain'],
      ['label' => 'Sweden', 'value' => 'Sweden'],
    ]);
  }

}
