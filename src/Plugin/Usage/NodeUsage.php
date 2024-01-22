<?php

namespace Drupal\cohesion\Plugin\Usage;

/**
 * Plugin for node usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "node_usage",
 *   name = @Translation("Node usage"),
 *   entity_type = "node",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class NodeUsage extends FieldableContentEntityUsageBase {

}
