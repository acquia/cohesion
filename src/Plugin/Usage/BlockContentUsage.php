<?php

namespace Drupal\cohesion\Plugin\Usage;

/**
 * Plugin for entity block content usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "block_content_usage",
 *   name = @Translation("Block content usage"),
 *   entity_type = "block_content",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"site_studio", "core"},
 *   can_be_excluded = FALSE
 * )
 */
class BlockContentUsage extends FieldableContentEntityUsageBase {

}
