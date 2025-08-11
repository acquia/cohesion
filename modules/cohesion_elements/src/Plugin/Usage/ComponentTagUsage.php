<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

/**
 * Component tag usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 *
 * @Usage(
 *   id = "cohesion_component_tag_usage",
 *   name = @Translation("Component tag usage"),
 *   entity_type = "cohesion_component_tag",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class ComponentTagUsage extends ElementTagUsageBase {

}
