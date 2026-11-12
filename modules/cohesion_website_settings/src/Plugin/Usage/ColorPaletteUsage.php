<?php

namespace Drupal\cohesion_website_settings\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Color palette usage plugin.
 *
 * @package Drupal\cohesion_website_settings\Plugin\Usage
 *
 * @Usage(
 *   id = "color_palette_usage",
 *   name = @Translation("Color palette usage"),
 *   entity_type = "cohesion_color",
 *   scannable = FALSE,
 *   scan_same_type = TRUE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class ColorPaletteUsage extends UsagePluginBase {

  // Use a regex to scan the JSON blog for usages for colors.
  const COLOR_MATCH_REGEX = '/(coh-color-([\w\-]*?))(\"|\s)/m';

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);
    $colors = [];

    // Get all the colors.
    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' || $entry['type'] == 'string') {
        preg_match_all(self::COLOR_MATCH_REGEX, $entry['value'], $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
          // Found a matching color.
          $colors[] = $match[2];
        }
      }
    }

    if (!empty($colors)) {
      // Find matching colors in the palette.
      foreach ($colors as $color) {
        if ($color_entity = $this->storage->load($color)) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $color_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
