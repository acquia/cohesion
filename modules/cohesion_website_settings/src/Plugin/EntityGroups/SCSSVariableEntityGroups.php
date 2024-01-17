<?php

namespace Drupal\cohesion_website_settings\Plugin\EntityGroups;

use Drupal\cohesion\EntityGroupsPluginBase;
use Drupal\cohesion_website_settings\Entity\SCSSVariable;
use Drupal\Component\Serialization\Json;

/**
 * Class SCSSVariableEntityGroups.
 *
 * This handles loading and saving back combined SCSS Variable entities with
 * single JSON object.
 *
 * @package Drupal\cohesion_website_settings\Plugin\EntiyGroups
 *
 * @EntityGroups(
 *   id = "scss_variable_entity_groups",
 *   name = @Translation("SCSS variables entity groups"),
 *   entity_type = "cohesion_scss_variable",
 * )
 */
class SCSSVariableEntityGroups extends EntityGroupsPluginBase {

  /**
   * {@inheritdoc}
   */
  public function saveFromModel($variables) {

    // Create any new entities.
    foreach ($variables->SCSSVariables as $variable) {

      // Does the entity already exist?
      $query = \Drupal::entityQuery('cohesion_scss_variable');
      $query->condition('id', $variable->uid)->accessCheck(TRUE);
      $entity_ids = $query->execute();

      // No? Then create and save it.
      if (!count($entity_ids) && isset($variable->value)) {
        /** @var \Drupal\cohesion_website_settings\Entity\SCSSVariable $entity */
        $entity = SCSSVariable::create([
          'id' => $variable->uid,
          'value' => $variable->value,
        ]);
        $entity->setDefaultValues();
        $entity->setJsonValue(Json::encode($variable));
        $entity->save();
      }
    }

    // Delete any removed entities.
    $query = \Drupal::entityQuery('cohesion_scss_variable')->accessCheck(TRUE);
    if ($entity_ids = $query->execute()) {
      foreach ($variables->SCSSVariables as $variable) {
        if (isset($entity_ids[$variable->uid])) {
          // Remove any found SCSS variables from the list to delete.
          unset($entity_ids[$variable->uid]);
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

    foreach ($variables->SCSSVariables as $variable) {
      if ($entity = $this->storage->load($variable->uid)) {
        $changed = FALSE;

        // Set the new weight.
        if ($entity->getWeight() !== $weight_index) {
          $changed = TRUE;
          $entity->setWeight($weight_index);
        }

        // JSON changes detected.
        if ($entity->getJsonValues() !== Json::encode($variable)) {
          $changed = TRUE;
        }

        if ($changed) {
          // Save the SCSS variable entity.
          $entity->setJsonValue(Json::encode($variable));
          $changed_entities[] = $entity;

          // And flag it, so entities that use it get resaved.
          $in_use_list = array_merge($in_use_list, $this->usageUpdateManager->getInUseEntitiesList($entity));
        }

        $weight_index += 1;
      }
    }

    // Re-order all SCSS variables.
    $this->reorderSCSSVariable($variables);

    // Return the data as an array to be mapped by list().
    return [$in_use_list, $changed_entities];
  }

  /**
   * Reorder the SCSS variables in the list by weight.
   *
   * @param $variables
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function reorderSCSSVariable($variables) {
    // Build the weighting array.
    $c = 0;
    $weights = [];
    foreach ($variables->SCSSVariables as $variable) {
      $weights[$variable->uid] = $c++;
    }

    // And re-order all the SCSS variables.
    /** @var \Drupal\cohesion_website_settings\Entity\SCSSVariable $entity */
    foreach ($this->storage->loadMultiple() as $entity) {
      if (isset($weights[$entity->id()])) {
        $entity->setWeight($weights[$entity->id()]);
        $entity->save();
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getGroupJsonValues() {
    $merged_variables = [];

    $variable_entities = $this->storage->loadMultiple();

    /** @var \Drupal\cohesion_website_settings\Entity\SCSSVariable $variable_entity */
    foreach ($variable_entities as $variable_entity) {

      $json_values = $variable_entity->getDecodedJsonValues();

      // Set in the inuse key.
      $json_values['inuse'] = count($this->usageUpdateManager->getInUseEntitiesList($variable_entity)) ? TRUE : FALSE;

      // Save the to the combined array.
      $weight = $variable_entity->getWeight();

      if (isset($merged_variables[$weight])) {
        while (isset($merged_variables[$weight])) {
          $weight++;
        }
      }

      $merged_variables[$weight] = $json_values;
    }

    // Remove the object keys (Angular expects the SCSS variables list to be an
    // array).
    ksort($merged_variables);
    $merged_variables = array_values($merged_variables);

    // Return JSON encoded.
    if (count($merged_variables)) {

      return Json::encode(['SCSSVariables' => $merged_variables]);
    }
    else {
      return '{}';
    }
  }

}
