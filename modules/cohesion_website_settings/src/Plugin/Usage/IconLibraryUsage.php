<?php

namespace Drupal\cohesion_website_settings\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Icon library usage plugin.
 *
 * @package Drupal\cohesion_website_settings\Plugin\Usage
 *
 * @Usage(
 *   id = "icon_libraries_usage",
 *   name = @Translation("Icon libraries usage"),
 *   entity_type = "cohesion_icon_library",
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
class IconLibraryUsage extends UsagePluginBase {

  /**
   * @var array
   */
  private $entities;

  /**
   * Implemented as a recursive function because the RecursiveIterator
   * only visits leaf nodes :/.
   *
   * @param $array
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function scanForIconLibraries($array) {
    foreach ($array as $key => $item) {
      // Match two siblings.
      if (isset($item['fontFamily']) && isset($item['iconName'])) {
        // Load the icon library entity to get it's UUID.
        // If it doesn't load via the font family, default to "custom"
        // for custom icon libraries.
        if ($icon_library_entity = $this->storage->load($item['fontFamily']) ?: $this->storage->load('custom')) {
          // Add to the list of dependencies.
          $this->entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $icon_library_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }

      if (is_array($item)) {
        // Recurse...
        $this->scanForIconLibraries($item);
      }
    }
  }

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
    $this->entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' && isset($entry['decoded'])) {
        // Search for icon fonts within the decoded layout canvas.
        $this->scanForIconLibraries($entry['decoded']);
      }
    }

    return $this->entities;
  }

}
