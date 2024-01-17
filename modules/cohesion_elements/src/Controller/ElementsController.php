<?php

namespace Drupal\cohesion_elements\Controller;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\file\Entity\File;
use Drupal\image\Entity\ImageStyle;

/**
 * Elements controller.
 *
 * @package Drupal\cohesion_elements\Controller
 */
class ElementsController {

  /**
   * Return list of all categories for a given element type.
   *
   * @param string $entity_type_id
   *   Type of category entity, i.e. helpers, components, etc.
   *
   * @param bool $bypass_permission_check
   *
   * @return array|bool
   *   Return array of category objects,
   *   or FALSE if element is not of a supported type.
   */
  public static function getElementCategories($entity_type_id, $bypass_permission_check = FALSE) {
    // Get list of categories sorte dby weight.
    try {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type_id);
    }
    catch (\Throwable $e) {
      return [];
    }

    $category_entities = $storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight')
      ->execute();
    $categories = [];

    if ($category_entities = $storage->loadMultiple($category_entities)) {
      /** @var ElementCategoryBase $entity */
      foreach ($category_entities as $entity) {
        if ($entity->hasGroupAccess() || $bypass_permission_check) {
          // Add to the array.
          $categories[$entity->id()] = [
            'label' => $entity->label(),
            'class' => $entity->getClass(),
            'id' => $entity->id(),
          ];
        }
      }
    }

    return $categories;
  }

  /**
   * @param $entity_type
   * @param $element_id
   * @return false|mixed|string
   */
  public static function getElementPreviewImageURL($entity_type, $element_id) {
    // Get list of entities matching the specified type.
    try {
      $storage = \Drupal::entityTypeManager()->getStorage($entity_type);
    }
    catch (PluginException $exception) {
      watchdog_exception('cohesion_elements', $exception);
    }

    if (isset($storage)) {
      $query = $storage->getQuery()
        ->accessCheck(TRUE)
        ->condition('id', $element_id, '=')
        ->range(0, 1);
      $ids = $query->execute();
      $entities = $storage->loadMultiple($ids);

      foreach ($entities as $entity) {
        // Check the component/helper has a preview image defined.
        if ($preview_image = $entity->getPreviewImage()) {
          if ($file = File::load($preview_image)) {
            if ($is = ImageStyle::load('dx8_component_preview')) {
              $url = $is->buildUrl($file->getFileUri());
              $url = parse_url($url);
              $decoded = $url['path'];

              if (isset($url['query']) && !empty($url['query'])) {
                $decoded .= '?' . $url['query'];
              }

              return $decoded;
            }
          } else {
            return FALSE;
          }
        }
      }
    }

    return FALSE;
  }

}
