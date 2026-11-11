<?php

namespace Drupal\cohesion_website_settings\Plugin\EntityGroups;

use Drupal\cohesion\EntityGroupsPluginBase;
use Drupal\cohesion_website_settings\Entity\FontLibrary;
use Drupal\cohesion_website_settings\Entity\FontStack;
use Drupal\Component\Serialization\Json;

/**
 * Class FontLibrariesEntityGroups.
 *
 * This handles loading and saving back combined FontLibrary and FontStack
 * entities with single JSON object.
 *
 * @package Drupal\cohesion_website_settings\Plugin\EntiyGroups
 *
 * @EntityGroups(
 *   id = "font_libraries_entity_groups",
 *   name = @Translation("Font libraries entity groups"),
 *   entity_type = "cohesion_font_library",
 * )
 */
class FontLibrariesEntityGroups extends EntityGroupsPluginBase {

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  public function saveFromModel($libraries) {
    $flush_caches = FALSE;
    $new_ids = [];

    // FontLibrary/FontStack: Create any new entities.
    foreach ($libraries as $source => $group) {
      foreach ($group as $index => $library) {

        switch ($source) {
          case 'fonts':
            // No? Then create and save it.
            if (property_exists($library, 'library') && !property_exists($library->library, 'uid') && property_exists($library->library, 'name')) {
              $hash = hash('md5', Json::encode($library->library));
              $libraries->{$source}[$index]->library->uid = $hash;

              /** @var \Drupal\cohesion_website_settings\Entity\FontLibrary $entity */
              $entity = FontLibrary::create([
                'id' => $hash,
                'label' => $library->library->name,
                'source' => $source,
              ]);
              $entity->setDefaultValues();
              $entity->setJsonValue(Json::encode($libraries->{$source}[$index]->library));
              $entity->save();

              // Store the id so we don't re-scan changes (it will fail because
              // of temporary file paths).
              $new_ids[] = $entity->id();
            }
            break;

          case 'fontStacks':
            // Does the entity already exist?
            $query = \Drupal::entityQuery('cohesion_font_stack')->accessCheck(TRUE);
            $query->condition('id', $library->stack->uid);
            $entity_ids = $query->execute();

            // No? Then create and save it.
            if (!count($entity_ids) && property_exists($library->stack, 'name')) {
              /** @var \Drupal\cohesion_website_settings\Entity\FontStack $entity */
              $entity = FontStack::create([
                'id' => $library->stack->uid,
                'label' => $library->stack->name,
                'source' => $source,
              ]);
              $entity->setDefaultValues();
              $entity->setJsonValue(Json::encode($library->stack));
              $entity->save();
            }
            break;
        }
      }
    }

    // FontLibrary: Delete any removed entities.
    $query = \Drupal::entityQuery('cohesion_font_library')->accessCheck(TRUE);
    if ($entity_ids = $query->execute()) {
      foreach ($libraries as $source => $group) {
        foreach ($group as $library) {
          if (property_exists($library, 'library') && property_exists($library->library, 'uid') && isset($entity_ids[$library->library->uid])) {
            // Remove any found libraries form the list to delete.
            unset($entity_ids[$library->library->uid]);
            // When font libraries are removed we need to flush the entire
            // cache, including the render caches.
          }
        }
      }

      // Now perform the delete.
      if ($entities_to_delete = $this->storage->loadMultiple($entity_ids)) {
        $flush_caches = TRUE;
        $this->storage->delete($entities_to_delete);
      }
    }

    // FontStack: Delete any removed entities.
    $query = \Drupal::entityQuery('cohesion_font_stack')->accessCheck(TRUE);
    if ($entity_ids = $query->execute()) {
      foreach ($libraries as $source => $group) {
        foreach ($group as $library) {
          if (property_exists($library, 'stack') && property_exists($library->stack, 'uid') && isset($entity_ids[$library->stack->uid])) {
            // Remove any found libraries form the list to delete.
            unset($entity_ids[$library->stack->uid]);
            // When font libraries are removed we need to flush the entire
            // cache, including the render caches.
          }
        }
      }

      // Now perform the delete.
      $font_stack_storage = $this->entityTypeManager->getStorage('cohesion_font_stack');
      if ($entities_to_delete = $font_stack_storage->loadMultiple($entity_ids)) {
        $flush_caches = TRUE;
        $font_stack_storage->delete($entities_to_delete);
      }
    }

    // Re-save any changed entities.
    $in_use_list = [];
    $changed_entities = [];

    foreach ($libraries as $source => $group) {
      foreach ($group as $library) {

        switch ($source) {
          case 'fonts':
            /** @var \Drupal\cohesion_website_settings\Entity\FontLibrary $entity */ if (property_exists($library, 'library') && !in_array($library->library->uid, $new_ids) && $entity = $this->storage->load($library->library->uid)) {
              // Check everything is the same.
              $entity_json_values = $entity->getJsonValues();

              // Changes detected.
              if ($entity_json_values !== Json::encode($library->library)) {
                // Save the entity.
                $entity->setJsonValue(Json::encode($library->library));
                $changed_entities[] = $entity;

                // Because there is no strong connection between font libraries
                // and font stacks, we have to flush the render cache when
                // libraries are changed.
                $flush_caches = TRUE;
              }
            }
            break;

          case 'fontStacks':
            if ($entity = $this->entityTypeManager->getStorage('cohesion_font_stack')->load($library->stack->uid)) {
              // Check everything is the same.
              $entity_json_values = $entity->getJsonValues();

              // Changes detected.
              // if (isset($library['stack']['inuse'])) {
              //  unset($library['stack']['inuse']);
              // }.
              if ($entity_json_values !== json_encode($library->stack, JSON_UNESCAPED_UNICODE)) {
                // Save the entity.
                $entity->setJsonValue(Json::encode($library->stack));
                $changed_entities[] = $entity;

                // And flag it, so entities that use it get resaved.
                $in_use_list = array_merge($in_use_list, $this->usageUpdateManager->getInUseEntitiesList($entity));
                $flush_caches = TRUE;
              }
            }
            break;
        }
      }
    }

    // Return the data as an array to be mapped by list().
    return [$in_use_list, $changed_entities, $flush_caches];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupJsonValues() {
    $merged_font_libraries = [];
    $count = 0;

    /** @var \Drupal\cohesion_website_settings\Entity\FontLibrary $font_library_entity */
    foreach ($this->storage->loadMultiple() as $font_library_entity) {
      $json_values = $font_library_entity->getDecodedJsonValues();

      // Save the to the combined array.
      $merged_font_libraries['fonts'][] = [
        'library' => $json_values,
      ];

      $count++;
    }

    /** @var \Drupal\cohesion_website_settings\Entity\FontStack $font_stack_entity */
    foreach ($this->entityTypeManager->getStorage('cohesion_font_stack')->loadMultiple() as $font_stack_entity) {
      $json_values = $font_stack_entity->getDecodedJsonValues();

      // Set in the inuse key.
      $json_values['inuse'] = count($this->usageUpdateManager->getInUseEntitiesList($font_stack_entity)) ? TRUE : FALSE;

      // Save the to the combined array.
      $merged_font_libraries['fontStacks'][] = [
        'stack' => $json_values,
      ];

      $count++;
    }

    // Return JSON encoded.
    if ($count > 0) {
      return Json::encode($merged_font_libraries);
    }
    else {
      return '{}';
    }

  }

}
