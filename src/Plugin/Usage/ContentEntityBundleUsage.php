<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for content entity bundle usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "content_entity_bundle_usage",
 *   name = @Translation("Content entity bundle usage"),
 *   entity_type = "node_type",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core"},
 *   can_be_excluded = FALSE
 * )
 */
class ContentEntityBundleUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Get content entity fields.
    $bundle_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('node', $entity->get('type'));

    foreach ($bundle_fields as $field) {
      $scannable[] = [
        'type' => 'drupal_field',
        'entity_type' => 'field_config',
        'uuid' => $field->getUniqueIdentifier(),
      ];
    }

    // Get form displays.
    $all_form_displays = EntityFormDisplay::loadMultiple();

    // Filter to only form displays for this entity type.
    $form_displays = [];
    foreach ($all_form_displays as $all_form_display) {
      if ($all_form_display->getTargetBundle() == $entity->get('type')) {
        $form_displays[] = $all_form_display;
      }
    }

    foreach ($form_displays as $form_display) {
      $scannable[] = [
        'type' => 'drupal_form_display',
        'entity_type' => 'entity_form_display',
        'uuid' => $form_display->uuid(),
      ];
    }

    // Get view displays.
    $all_view_displays = EntityViewDisplay::loadMultiple();
    // Filter to only view displays for this entity type.
    $view_displays = [];
    foreach ($all_view_displays as $all_view_display) {
      if ($all_view_display->getTargetBundle() == $entity->get('type')) {
        $view_displays[] = $all_view_display;
      }
    }

    foreach ($view_displays as $view_display) {
      $scannable[] = [
        'type' => 'drupal_view_display',
        'entity_type' => 'entity_view_display',
        'uuid' => $view_display->uuid(),
      ];
    }

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'node_type') {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entry['uuid'],
        ];
      }
    }

    return $entities;
  }

}
