<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Helper usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
#[Usage(
  id: "cohesion_helper_usage",
  name: new TranslatableMarkup("Helper usage"),
  entity_type: "cohesion_helper",
  scannable: TRUE,
  scan_same_type: TRUE,
  group_key: "category",
  group_key_entity_type: "cohesion_helper_category",
  exclude_from_package_requirements: FALSE,
  exportable: TRUE,
  config_type: "site_studio",
  can_be_excluded: TRUE,
  scan_groups: ["site_studio"]
)]
class HelperUsage extends ElementUsageBase {

}
