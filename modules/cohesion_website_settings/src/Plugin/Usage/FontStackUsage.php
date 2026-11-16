<?php

namespace Drupal\cohesion_website_settings\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Font stack usage plugin.
 *
 * @package Drupal\cohesion_website_settings\Plugin\Usage
 *
 * @Usage(
 *   id = "font_stack_usage",
 *   name = @Translation("Font stack usage"),
 *   entity_type = "cohesion_font_stack",
 *   scannable = TRUE,
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
class FontStackUsage extends UsagePluginBase {

  // Use a regex to scan the JSON blog for usages for colors.
  const FONT_STACK_MATCH_REGEX = '/(\$coh-font-([\w\-]*?))(\"|\s)/m';

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

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);
    $font_stacks = [];

    // Get all the colors.
    foreach ($data as $entry) {
      if (isset($entry['type'])) {
        if ($entry['type'] == 'json_string') {
          preg_match_all(self::FONT_STACK_MATCH_REGEX, $entry['value'], $matches, PREG_SET_ORDER, 0);

          foreach ($matches as $match) {
            // Found a matching color.
            $font_stacks[] = $match[2];
          }
        }
      }
    }

    if (!empty($font_stacks)) {
      // Find matching colors in the palette.
      foreach ($font_stacks as $font_stack) {
        if ($font_stack_entity = $this->storage->load($font_stack)) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $font_stack_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
