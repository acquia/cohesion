<?php

namespace Drupal\cohesion_style_helpers\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Utility\Error;

/**
 * Class StyleHelpersEndpointController.
 *
 * An endpoint to return a list of style helpers entities.
 *
 * @package Drupal\cohesion_style_helpers\Controller
 */
class StyleHelpersEndpointController extends ControllerBase {

  /**
   * @return \Drupal\cohesion\CohesionJsonResponse $response json containing data for all variables
   */
  public function getAll() {
    $style_helpers = [];
    if (($entities = $this->styleHelperEntities())) {
      foreach ($entities as $entity) {

        if (!isset($style_helpers[$entity->getCustomStyleType()])) {
          try {
            $custom_group_entity = \Drupal::entityTypeManager()->getStorage('custom_style_type')->load($entity->getCustomStyleType());
          }
          catch (PluginNotFoundException $ex) {
            Error::logException('cohesion', $ex);
            $custom_group_entity = NULL;
          }
          $style_helpers[$entity->getCustomStyleType()] = [
            "keyName" => $entity->getCustomStyleType(),
            "title" => $custom_group_entity ? $custom_group_entity->label() : NULL,
            "options" => [],
          ];
        }

        $style_helpers[$entity->getCustomStyleType()]["options"][] = [
          "keyName" => $entity->id(),
          "title" => $entity->label(),
          "noCheckbox" => "true",
          "useCallback" => "true",
        ];
      }
    }

    if (!empty($style_helpers)) {
      ksort($style_helpers);
      $style_helpers = array_values($style_helpers);
    }

    $error = empty($style_helpers) ? TRUE : FALSE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $style_helpers,
    ]);
  }

  /**
   * @return \Drupal\cohesion\CohesionJsonResponse $response json containing data for all variables
   */
  public function getOne($style_helper_id) {
    $data = [];
    if (($style_helper = $this->styleHelperEntity($style_helper_id))) {
      /** @var \Drupal\cohesion_style_helpers\Entity\StyleHelper $style_helper */
      $data['styles'] = $style_helper->getDecodedJsonValues(TRUE);
      $data['mapper'] = $style_helper->getDecodedJsonMapper();
    }

    $error = empty($data) ? TRUE : FALSE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   * POST: /cohesionapi/style-helper-save
   * Save an element given the provided details.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function styleHelperSave(Request $request) {

    $content_raw = $request->getContent();
    $content = json_decode($content_raw);

    // Validate input data.
    if (!$this->validateInputData($content)) {
      return new CohesionJsonResponse([
        'status' => 'error',
        'data' => ['error' => $this->t('Bad request')],
      ], 400);
    }

    $values = [
      'label' => $content->label,
      'custom_style_type' => $content->category,
      'status' => TRUE,
      'json_values' => $content->json_values,
      'json_mapper' => $content->json_mapper,
    ];

    $entity_type_id = 'cohesion_style_helper';
    // Check title is unique.
    if ($this->checkDuplicateByLabel('cohesion_style_helper', $values['label']) > 0) {
      return new CohesionJsonResponse([
        'status' => 'error',
        'data' => ['error' => $this->t('There is already a style helper with that name, please choose a different name.')],
      ], 400);
    }

    if ($values && is_array($values) && $this->createStyleHelper($entity_type_id, $values)) {
      return new CohesionJsonResponse([
        'status' => 'success',
        'data' => ['success' => $this->t('Style helper saved')],
      ]);
    }
    else {
      // No values or incorrect value format.
      return new CohesionJsonResponse([
        'status' => 'error',
        'data' => ['error' => $this->t('Style helper not saved')],
      ], 400);
    }
  }

  /**
   *
   * @return array of style helper entities
   */
  private function styleHelperEntities() {
    try {
      $ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_style_helper')->getQuery()
        ->accessCheck(TRUE)
        ->condition('status', TRUE)
        ->condition('selectable', TRUE)
        ->execute();

      return \Drupal::service('entity_type.manager')->getStorage('cohesion_style_helper')->loadMultiple($ids);
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }
    return [];
  }

  /**
   *
   * @return style helper entity or null if entity not found
   */
  private function styleHelperEntity($entity_id) {
    try {
      return \Drupal::service('entity_type.manager')->getStorage('cohesion_style_helper')->load($entity_id);
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }
    return NULL;
  }

  /**
   *
   * @param $content
   *
   * @return bool
   */
  private function validateInputData($content) {
    $validate_keys = ['label', 'category', 'json_values', 'json_mapper'];
    $valid = TRUE;
    foreach ($validate_keys as $key) {
      if (!property_exists($content, $key) || is_null($content->{$key}) || empty($content->{$key})) {
        $valid = FALSE;
        break;
      }
    }
    return $valid;
  }

  /**
   *
   * @param string $entity_type_id
   * @param string $label
   *
   * @return int
   */
  private function checkDuplicateByLabel($entity_type_id, $label) {
    try {
      return \Drupal::entityQuery($entity_type_id)
        ->accessCheck(TRUE)
        ->condition('label', trim($label), '=')
        ->count()
        ->execute();
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }
    return 0;
  }

  /**
   *
   * @param string $entity_type_id
   * @param array $values
   *
   * @return int entity_id|boolean
   */
  private function createStyleHelper($entity_type_id, $values = []) {
    try {
      // Create the entity object.
      $entity = \Drupal::entityTypeManager()->getStorage($entity_type_id)->create($values);

      // Set entity id.
      $entity->set('id', implode('-', [
        $entity->get('custom_style_type'),
        hash('crc32b', $entity->uuid()),
      ]));

      // Set other entity values.
      $entity->set('status', TRUE);
      $entity->set('selectable', TRUE);
      $entity->set('modified', TRUE);

      // Save.
      $entity->save();
      return $entity->id();
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }
    return FALSE;
  }

}
