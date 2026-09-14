<?php

namespace Drupal\cohesion_custom_styles\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;

/**
 * Defines the custom style type configuration entity.
 *
 * @ConfigEntityType(
 *   id = "custom_style_type",
 *   label = @Translation("Custom style type"),
 *   label_singular = @Translation("Custom style type"),
 *   label_plural = @Translation("Custom style types"),
 *   label_count = @PluralTranslation(
 *     singular = "@count custom style type",
 *     plural = "@count custom style types",
 *   ),
 *   config_prefix = "cohesion_custom_style_type",
 *   admin_permission = "administer custom styles",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "element"
 *   }
 * )
 */
class CustomStyleType extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'custom_styles_type';

  /**
   * The machine name of this custom style type (received from the API as
   * "element_id")
   *
   * @var string
   */
  protected $id;

  /**
   * The human-readable name of the custom style type (received from the API
   * as: "element_label")
   *
   * @var string
   */
  protected $label;

  /**
   * The HTML element that represents this custom style type
   * (h1, cite, ol, etc.)
   *
   * @var string
   */
  protected $element;

  /**
   * Return the element type
   * (for cohesion_custom_styles/add)
   */
  public function getElement() {
    return $this->element;
  }

  /**
   * Set the element type
   * (for cohesion_custom_styles/add)
   */
  public function setElement($element) {
    $this->element = $element;
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
    return TRUE;
  }

  /**
   * Create (merge) CustomStyleType entities from the list in $entities
   * (populated from hook_install() and/or cohesion_custom_styles_process_batch
   * in .module)
   * Add new entities and remove defunct ones.
   *
   * @param $entities
   *
   * @return bool
   */
  public static function importCustomStyleTypeEntities($entities) {

    // Check if we have any entities to import.
    if (!is_array($entities) || !count($entities)) {
      return FALSE;
    }

    $entity_storage = \Drupal::entityTypeManager()->getStorage('custom_style_type');

    // Import each entity.
    $canonical_list = [];
    foreach ($entities as $e) {
      // Check to see if this entity exists.
      $entity_ids = \Drupal::entityQuery('custom_style_type')
        ->accessCheck(TRUE)
        ->condition('id', $e['element_id'])
        ->execute();

      // New, so create.
      if (!count($entity_ids)) {
        $entity = $entity_storage->create([
          'id' => $e['element_id'],
          'label' => $e['element_label'],
          'element' => $e['element_element'],
        ]);
      }
      // Exists, so update.
      else {
        $entity = $entity_storage->load($e['element_id']);
        $entity->set('label', $e['element_label']);
        $entity->set('element', $e['element_element']);
      }

      $entity->save();

      // Add to the array to compare (for merge).
      $canonical_list[$e['element_id']] = $e['element_label'];
    }

    // Remove old entities.
    $current_entities = CustomStyleType::loadMultiple();
    foreach ($current_entities as $k => $v) {
      if (!array_key_exists($k, $canonical_list)) {
        $v->delete();
      }
    }

    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

  /**
   * @inheritdoc
   */
  public function getApiPluginInstance() {}

}
