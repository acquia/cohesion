<?php

namespace Drupal\cohesion_website_settings\Plugin\EntityGroups;

use Drupal\cohesion\EntityGroupsPluginBase;
use Drupal\cohesion_website_settings\Entity\Color;
use Drupal\Component\Serialization\Json;

/**
 * Class ColorEntityGroups.
 *
 * This handles loading and saving back combined Colors entities with single
 * JSON object.
 *
 * @package Drupal\cohesion_website_settings\Plugin\EntiyGroups
 *
 * @EntityGroups(
 *   id = "color_entity_groups",
 *   name = @Translation("Color entity groups"),
 *   entity_type = "cohesion_color",
 * )
 */
class ColorEntityGroups extends EntityGroupsPluginBase {

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  public function saveFromModel($colors) {

    // Create any new entities.
    foreach ($colors->colors as $color) {
      // Does the entity already exist?
      $query = $this->storage->getQuery()->accessCheck(TRUE);
      $query->condition('id', $color->uid);
      $entity_ids = $query->execute();

      // No? Then create and save it.
      if (!count($entity_ids) && isset($color->name)) {
        /** @var \Drupal\cohesion_website_settings\Entity\Color $entity */
        $entity = Color::create([
          'id' => $color->uid,
          'label' => $color->name,
        ]);
        $entity->setDefaultValues();
        $entity->setJsonValue(Json::encode($color));
        $entity->save();
      }
    }

    // Delete any removed entities.
    $query = $this->storage->getQuery()->accessCheck(TRUE);
    if ($entity_ids = $query->execute()) {
      foreach ($colors->colors as $color) {
        if (isset($entity_ids[$color->uid])) {
          // Remove any found colors from the list to delete.
          unset($entity_ids[$color->uid]);
        }
      }

      // Now perform the delete.
      if ($entities_to_delete = $this->storage->loadMultiple($entity_ids)) {
        $this->storage->delete($entities_to_delete);
      }
    }

    // Re-save any changed entities.
    $in_use_list = [];
    $changed_entities = [];
    $weight_index = 0;

    foreach ($colors->colors as $color) {
      if ($entity = $this->storage->load($color->uid)) {
        $changed = FALSE;

        // Set the new weight.
        if ($entity->getWeight() !== $weight_index) {
          $changed = TRUE;
          $entity->setWeight($weight_index);
        }

        // JSON changes detected.
        if ($entity->getJsonValues() !== Json::encode($color)) {
          $changed = TRUE;
        }

        if ($changed) {
          // Save the color entity.
          $entity->setJsonValue(Json::encode($color));
          $changed_entities[] = $entity;

          // And flag it, so entities that use it get resaved.
          $in_use_list = array_merge($in_use_list, $this->usageUpdateManager->getInUseEntitiesList($entity));
        }

        $weight_index += 1;
      }
    }

    // Return the data as an array to be mapped by list().
    return [$in_use_list, $changed_entities];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupJsonValues() {
    $merged_colors = [];

    $color_entities = $this->storage->loadMultiple();

    /** @var \Drupal\cohesion_website_settings\Entity\Color $color_entity */
    foreach ($color_entities as $color_entity) {
      $json_values = $color_entity->getDecodedJsonValues();

      // Set in the inuse key.
      $json_values['inuse'] = count($this->usageUpdateManager->getInUseEntitiesList($color_entity)) ? TRUE : FALSE;

      // Save the to the combined array.
      $weight = $color_entity->getWeight();

      if (isset($merged_colors[$weight])) {
        while (isset($merged_colors[$weight])) {
          $weight++;
        }
      }

      $merged_colors[$weight] = $json_values;
    }

    // Remove the object keys (Angular expects the color list to be an array).
    ksort($merged_colors);
    $merged_colors = array_values($merged_colors);

    // Return JSON encoded.
    if (count($merged_colors)) {
      return Json::encode(['colors' => $merged_colors]);
    }
    else {
      return '{}';
    }
  }

}
