<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for menu usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "menu_usage",
 *   name = @Translation("Menu usage"),
 *   entity_type = "menu",
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
class MenuUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    return [];
  }

}
