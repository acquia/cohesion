<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Helper category usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
#[Usage(
  id: "cohesion_helper_category_usage",
  name: new TranslatableMarkup("Helper category usage"),
  entity_type: "cohesion_helper_category",
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
class HelperCategoryUsage extends ElementCategoryUsageBase {

}
