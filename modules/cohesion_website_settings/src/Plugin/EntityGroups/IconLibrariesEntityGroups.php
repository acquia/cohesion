<?php

namespace Drupal\cohesion_website_settings\Plugin\EntityGroups;

use Drupal\cohesion\EntityGroupsPluginBase;
use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\Component\Serialization\Json;

/**
 * Class IconLibrariesEntityGroups.
 *
 * Handles loading and saving back groups of IconLibrary entities with a single
 * JSON object.
 *
 * @package Drupal\cohesion_website_settings\Plugin\EntiyGroups
 *
 * @EntityGroups(
 *   id = "icon_libraries_entity_groups",
 *   name = @Translation("Icon libraries entity groups"),
 *   entity_type = "cohesion_icon_library",
 * )
 */
class IconLibrariesEntityGroups extends EntityGroupsPluginBase {

  /**
   * {@inheritdoc}
   *
   * @testme
   */
  public function saveFromModel($libraries) {
    $new_ids = [];

    $in_use_list = [];
    $changed_entities = [];

    if (property_exists($libraries, 'iconLibraries') && is_array($libraries->iconLibraries)) {
      // Remove any unfilled entries from the libraries.
      foreach ($libraries->iconLibraries as $index => $library) {
        // Icon library should have name,key and provider attributes.
        if (!property_exists($library, 'library') || !property_exists($library->library, 'name') || !property_exists($library->library, 'key') || !property_exists($library->library, 'provider')) {
          unset($libraries->iconLibraries[$index]);
        }
      }

      // Create any new entities.
      foreach ($libraries->iconLibraries as $index => $library) {
        // Does the entity already exist?
        $entity_count = $this->storage->getQuery()
          ->accessCheck(TRUE)
          ->condition('id', $library->library->provider)
          ->count()
          ->execute();

        // No? Then create and save it.
        if ($entity_count === 0) {

          // Make sure no other entity has the same name.
          $entity_by_name_count = $this->storage->getQuery()
            ->accessCheck(TRUE)
            ->condition('label', $library->library->name)
            ->count()
            ->execute();

          if ($entity_by_name_count === 0) {
            /** @var \Drupal\cohesion_website_settings\Entity\IconLibrary $entity */
            $entity = IconLibrary::create([
              'id' => $library->library->provider,
              'label' => $library->library->name,
            ]);
            $entity->setDefaultValues();
            $entity->setJsonValue(Json::encode($library->library));
            try {
              $entity->save();
            }
            catch (\Exception $e) {
              // User added two entities of the same type to each section.
              \Drupal::messenger()->addError(t('@error You cannot add two icons of the same type.', ['@error' => $e->getMessage()]));
            }

          }
          else {
            // User added two entities of the same name.
            \Drupal::messenger()->addError(t('You cannot add two icons with the same name.'));
          }

          // Store the id so we don't re-scan changes (it will fail because of
          // temporary file paths).
          $new_ids[] = $index;

        }
      }

      // Delete any removed entities.
      $query = $this->storage->getQuery()->accessCheck(TRUE);
      if ($to_delete_entity_ids = $query->execute()) {
        foreach ($libraries->iconLibraries as $library) {
          if (isset($to_delete_entity_ids[$library->library->provider])) {
            // Remove any found libraries from the list to delete.
            unset($to_delete_entity_ids[$library->library->provider]);
          }
        }

        // Now perform the delete.
        if ($entities_to_delete = $this->storage->loadMultiple($to_delete_entity_ids)) {
          $this->storage->delete($entities_to_delete);
        }
      }

      // Re-save any changed entities.
      foreach ($libraries->iconLibraries as $index => $library) {
        if (!in_array($index, $new_ids) && $entity = $this->storage->load($library->library->provider)) {
          // Check everything is the same.
          $entity_json_values = $entity->getJsonValues();

          // Changes detected.
          if (property_exists($library->library, 'inuse')) {
            unset($library->library->inuse);
          }

          if ($entity_json_values !== Json::encode($library->library)) {
            // Save the entity.
            $entity->setJsonValue(Json::encode($library->library));
            $changed_entities[] = $entity;

            // And flag it, so entities that use it get resaved.
            $in_use_list = array_merge($in_use_list, $this->usageUpdateManager->getInUseEntitiesList($entity));
          }
        }
      }
    }

    // Return the data as an array to be mapped by list().
    return [$in_use_list, $changed_entities];
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupJsonValues() {

    $merged_icon_libraries = [];

    /** @var \Drupal\cohesion_website_settings\Entity\IconLibrary $icon_library_entity */
    $count = 0;

    foreach ($this->storage->loadMultiple() as $icon_library_entity) {
      $json_values = $icon_library_entity->getDecodedJsonValues();

      // Set in the inuse key.
      $json_values['inuse'] = count($this->usageUpdateManager->getInUseEntitiesList($icon_library_entity)) ? TRUE : FALSE;

      // Save the to the combined array.
      $merged_icon_libraries['iconLibraries'][] = [
        'library' => $json_values,
      ];

      $count++;
    }

    // Return JSON encoded.
    if ($count > 0) {
      return Json::encode($merged_icon_libraries);
    }
    else {
      return '{}';
    }

  }

}
