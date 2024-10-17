<?php

namespace Drupal\cohesion_style_helpers\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Style helpers usage plugin.
 *
 * @package Drupal\cohesion_style_helpers\Plugin\Usage
 *
 * @Usage(
 *   id = "style_helpers_usage",
 *   name = @Translation("Style helpers usage"),
 *   entity_type = "cohesion_style_helper",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = "custom_style_type",
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class StyleHelpersUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scanable_data = [];

    // Always add the JSON model and form blobs.
    $scanable_data[] = [
      'type' => 'json_string',
      'value' => $entity->getJsonValues(),
      'decoded' => $entity->getDecodedJsonValues(),
    ];

    return $scanable_data;
  }

}
