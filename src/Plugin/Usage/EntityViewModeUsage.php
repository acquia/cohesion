<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for entity view mode usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "entity_view_mode",
 *   name = @Translation("Entity view mode usage"),
 *   entity_type = "entity_view_mode",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = "targetEntityType",
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core"},
 *   can_be_excluded = FALSE
 * )
 */
class EntityViewModeUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {

      if ($entry['type'] == 'drupal_view_mode') {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entry['uuid'],
        ];
      }
    }

    return $entities;
  }

}
