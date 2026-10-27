<?php

namespace Drupal\cohesion_custom_styles\Controller;

use Drupal\cohesion\CohesionJsonResponse;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\cohesion_custom_styles\Entity\CustomStyleType;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Utility\Error;

/**
 * Class CustomStylesEndpointController.
 *
 * An endpoint to return a list of custom style entities.
 *
 * @package Drupal\cohesion_custom_styles\Controller
 */
class CustomStylesEndpointController extends ControllerBase {

  /**
   * Maps config entity to something Angular expects.
   *
   * @param $entity
   *
   * @return object
   */
  private function entityToMapped(CustomStyle $entity) {
    $mapped_object = new \StdClass();
    $mapped_object->label = $entity->get('label');
    $class_name = $entity->get('class_name');
    $mapped_object->value = ltrim($class_name, '.');
    $mapped_object->id = $entity->get('id');
    $mapped_object->edit_form = $entity->toUrl('edit-form')->toString();
    // Add custom style group.
    $type_id = $entity->get('custom_style_type');
    if (($custom_style_type_entity = $this->customStyleTypeEntityById($type_id)) && 'generic' === strtolower($custom_style_type_entity->get('label'))) {
      $mapped_object->group = $custom_style_type_entity->get('label');
    }
    return $mapped_object;
  }

  /**
   * Get all custom styles.
   *
   * @param \Drupal\cohesion_custom_styles\Entity\CustomStyleType|null $custom_style_type
   *
   * @return \Drupal\cohesion\CohesionJsonResponse
   */
  public function getCustomStyles(?CustomStyleType $custom_style_type = NULL) {
    $data = $this->customStyleEntities($custom_style_type);
    $error = !empty($data) ? FALSE : TRUE;
    $response = new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
    $response->setEncodingOptions(JSON_UNESCAPED_SLASHES);

    return $response;
  }

  /**
   * Maps config entity to something Angular expects.
   *
   * @param $entity
   *
   * @return object
   */
  private function customStyleTypeToMapped(CustomStyleType $entity) {
    $mapped_object = new \StdClass();
    $mapped_object->label = $entity->get('label');
    $mapped_object->value = $entity->get('id');
    // Add custom style group.
    $type_id = $entity->get('custom_style_type');
    if ($type_id && ($custom_style_type_entity = $this->customStyleTypeEntityById($type_id)) && 'generic' === strtolower($custom_style_type_entity->get('label'))) {
      $mapped_object->group = $custom_style_type_entity->get('label');
    }
    return $mapped_object;
  }

  /**
   * Get all custom style types.
   *
   * @return $response json containing data for all variables
   */
  public function customStyleTypeAll() {
    $custom_style_types_data = [];
    foreach ($custom_style_types = $this->entityTypeManager()->getStorage('custom_style_type')->loadMultiple() as $type) {
      $custom_style_types_data[$type->id()] = $this->customStyleTypeToMapped($type);
    }

    ksort($custom_style_types_data);
    $data = array_values($custom_style_types_data);
    $error = !empty($data) ? FALSE : TRUE;
    return new CohesionJsonResponse([
      'status' => !$error ? 'success' : 'error',
      'data' => $data,
    ]);
  }

  /**
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   * @param array $child_ids
   *
   * @return array of custom styles children
   */
  private function getStyleChildren(EntityStorageInterface $storage, $child_ids = []) {
    $children = [];
    if ($child_ids) {
      foreach ($storage->loadMultiple($child_ids) as $child_entity) {
        if ($child_entity->isSelectable()) {
          $children[] = $this->entityToMapped($child_entity);
        }
      }
    }
    return $children;
  }

  /**
   *
   * @param \Drupal\cohesion_custom_styles\Entity\CustomStyleType $custom_style_type
   *
   * @return array
   */
  private function customStyleEntities(?CustomStyleType $custom_style_type = NULL) {
    $data = [];
    try {
      $storage = $this->entityTypeManager()->getStorage('cohesion_custom_style');

      // Top level query.
      $query = $storage->getQuery()->accessCheck(TRUE)->sort('weight')->notExists('parent');

      if ($custom_style_type) {
        // Add Generic custom style to all custom style list.
        $condition_value = [
          $custom_style_type->get('id'),
          'generic',
        ];
        $query->condition('custom_style_type', $condition_value, 'IN');
      }
      // Get only enabled custom styles
      // And custom styles with enable selection turned off so we can catch
      // selectable children but keep the order (parent/children)
      $entity_ids = $query->condition('status', TRUE)->accessCheck(TRUE)->execute();

      // Execute the query.
      if (($entities = $storage->loadMultiple($entity_ids))) {
        /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyle $entity */
        foreach ($entities as $entity) {
          // Build the object.
          $mapped_object = $this->entityToMapped($entity);

          // Add the parent if it is selectable.
          if ($entity->isSelectable()) {
            $data = array_merge($data, [$mapped_object]);
          }

          // Build the children.
          $child_ids = $storage->getQuery()
            ->accessCheck(TRUE)
            ->condition('parent', $entity->getClass())
            ->condition('status', TRUE)
            ->condition('selectable', TRUE)
            ->sort('weight')
            ->execute();

          $child_objects = [];
          if (count($child_ids) > 0) {
            $child_objects = $this->getStyleChildren($storage, $child_ids);
          }

          // Add the children.
          $data = array_merge($data, $child_objects);
        }
      }
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }

    return $data;
  }

  /**
   *
   * @param string custom style type $id
   *
   * @return \Drupal\cohesion_custom_styles\Entity\CustomStyleType|NULL
   *
   */
  private function customStyleTypeEntityById($id) {
    try {
      return $this->entityTypeManager()->getStorage('custom_style_type')->load($id);
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
    }
    return NULL;
  }

}
