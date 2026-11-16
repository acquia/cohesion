<?php

namespace Drupal\cohesion_website_settings\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Website settings usage plugin.
 *
 * @package Drupal\cohesion_website_settings\Plugin\Usage
 *
 * @Usage(
 *   id = "cohesion_website_settings_usage",
 *   name = @Translation("Website settings usage"),
 *   entity_type = "cohesion_website_settings",
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
class WebsiteSettingsUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    // Always add the JSON model and form blobs.
    $scanable_data = [
      [
        'type' => 'json_string',
        'value' => $entity->getJsonValues(),
        'decoded' => $entity->getDecodedJsonValues(),
      ],
    ];

    return $scanable_data;
  }

}
