<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin for user role usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 */
#[Usage(
  id: "role_usage",
  name: new TranslatableMarkup("Role usage"),
  entity_type: "user_role",
  scannable: TRUE,
  scan_same_type: FALSE,
  group_key: FALSE,
  group_key_entity_type: FALSE,
  exclude_from_package_requirements: FALSE,
  exportable: TRUE,
  config_type: "core",
  can_be_excluded: FALSE,
  scan_groups: ["core", "site_studio"]
)]
class RoleUsage extends UsagePluginBase {

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
