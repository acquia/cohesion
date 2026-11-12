<?php

namespace Drupal\cohesion_website_settings\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class FontLibraryUsage.
 *
 * Only used to track font files. Font libraries are treated as a sort of
 * quasi-global entity with no explicit dependencies.
 *
 * @package Drupal\cohesion_website_settings\Plugin\Usage
 *
 * @Usage(
 *   id = "font_library_usage",
 *   name = @Translation("Font stack usage"),
 *   entity_type = "cohesion_font_library",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class FontLibraryUsage extends UsagePluginBase {

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
