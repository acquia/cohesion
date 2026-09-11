<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Component usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
#[Usage(
  id: "cohesion_component_usage",
  name: new TranslatableMarkup("Component usage"),
  entity_type: "cohesion_component",
  scannable: TRUE,
  scan_same_type: TRUE,
  group_key: "category",
  group_key_entity_type: "cohesion_component_category",
  exclude_from_package_requirements: FALSE,
  exportable: TRUE,
  config_type: "site_studio",
  can_be_excluded: TRUE,
  scan_groups: ["core", "site_studio"],
)]
class ComponentUsage extends ElementUsageBase {

}
