<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for entity view display usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "entity_view_display",
 *   name = @Translation("Entity view display usage"),
 *   entity_type = "entity_view_display",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = "targetEntityType,bundle",
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core"},
 *   can_be_excluded = FALSE
 * )
 */
class EntityViewDisplayUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Get all view modes.
    $all_view_modes = EntityViewMode::loadMultiple();

    $view_modes = [];
    foreach ($all_view_modes as $view_mode) {
      if ($entity->getTargetEntityTypeId() == $view_mode->getTargetType()) {
        // Get the view mode ID from the view and take just the view mode.
        $view_mode_id = substr(strstr($view_mode->id(), '.'), 1);
        if ($view_mode_id == $entity->getMode()) {
          $view_modes[] = $view_mode;
        }
      }
    }

    foreach ($view_modes as $view_mode) {
      $scannable[] = [
        'type' => 'drupal_view_mode',
        'entity_type' => 'entity_view_mode',
        'uuid' => $view_mode->uuid(),
      ];
    }

    // Find the associated entity types.
    $bundle_entity_type = \Drupal::entityTypeManager()->getStorage($entity->getTargetEntityTypeId())->getEntityType()->getBundleEntityType();
    $bundle = \Drupal::entityTypeManager()->getStorage($bundle_entity_type)->load($entity->getTargetBundle());

    $scannable[] = [
      'type' => $bundle->getEntityTypeId(),
      'entity_type' => $entity->getTargetBundle(),
      'uuid' => $bundle->uuid(),
    ];

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {

      if ($entry['type'] == 'drupal_view_display') {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entry['uuid'],
        ];
      }
    }

    return $entities;
  }

}
