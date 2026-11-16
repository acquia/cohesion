<?php

namespace Drupal\cohesion_sync\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Sync package usage plugin.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "package",
 *   name = @Translation("Package usage"),
 *   entity_type = "cohesion_sync_package",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class PackageUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    $settings = $entity->getSettings() ? $entity->getSettings() : [];

    foreach ($settings as $list) {
      if (isset($list['items'])) {
        foreach ($list['items'] as $uuid => $item) {
          $scannable[] = [
            'type' => 'entity_uuid',
            'entity_type' => $item['type'],
            'uuid' => $uuid,
          ];
        }
      }
    }

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    return [];
  }

}
