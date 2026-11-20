<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Component category usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
#[Usage(
  id: "cohesion_component_category_usage",
  name: new TranslatableMarkup("Component category usage"),
  entity_type: "cohesion_component_category",
  scannable: FALSE,
  scan_same_type: FALSE,
  group_key: FALSE,
  group_key_entity_type: FALSE,
  exclude_from_package_requirements: FALSE,
  exportable: TRUE,
  config_type: "site_studio",
  can_be_excluded: TRUE,
  scan_groups: ["site_studio"],
)]
class ComponentCategoryUsage extends ElementCategoryUsageBase {

}
