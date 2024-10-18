<?php

namespace Drupal\cohesion_custom_styles\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Custom style usage plugin.
 *
 * @package Drupal\cohesion_custom_styles\Plugin\Usage
 *
 * @Usage(
 *   id = "custom_styles_usage",
 *   name = @Translation("Custom styles usage"),
 *   entity_type = "cohesion_custom_style",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = "custom_style_type",
 *   group_key_entity_type = "custom_style_type",
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class CustomStylesUsage extends UsagePluginBase {

  // Use a regex to scan the JSON blog for usages for colors.
  const CUSTOM_STYLE_MATCH_REGEX = '/([^A-Za-z0-9\-_])(coh-style-(.*?))([^A-Za-z0-9\-_]|\\\\u0022)/m';

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    /** @var \Drupal\cohesion_custom_styles\Entity\CustomStyle $entity */
    $scanable_data = [];

    // Always add the JSON model and form blobs.
    $scanable_data[] = [
      'type' => 'json_string',
      'value' => $entity->getJsonValues(),
      'decoded' => $entity->getDecodedJsonValues(),
    ];

    // Add the parent as scannable if it's an extended style.
    if ($entity->getParent()) {
      $scanable_data[] = [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_custom_style',
        'id' => $entity->getParentId(),
      ];
    }

    // Get child styles.
    return $scanable_data;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);
    $custom_style_classes = [];

    // Get all the custom styles used used.
    foreach ($data as $entry) {
      // Search cohesion_layout canvases and potentialy WYSIWYG content.
      if (in_array($entry['type'], ['json_string', 'string'])) {
        // Cheaply patch the JSON.
        $entry['value'] = str_replace('\\/', '/', $entry['value']);

        preg_match_all(self::CUSTOM_STYLE_MATCH_REGEX, $entry['value'], $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
          // Found a matching color.
          $custom_style_classes[] = '.' . $match[2];
        }
      }

      // See if this is referencing a custom style.
      if ($entry['type'] == 'entity_id' && $entry['entity_type'] == 'cohesion_custom_style' && isset($entry['id']) && $entry['id'] !== NULL && $entry['id'] !== FALSE) {
        if ($custom_styles_entity = $this->storage->load($entry['id'])) {
          // Add directly to the list of entity dependencies.
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $custom_styles_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }

    }

    // Load all matching custom styles.
    if (!empty($custom_style_classes)) {
      // Strip duplicates.
      $custom_style_classes = array_unique($custom_style_classes);

      // Add them to the dependencies.
      $ids = $this->storage->getQuery('IN')
        ->accessCheck(TRUE)
        ->condition('class_name', $custom_style_classes)
        ->execute();

      foreach ($this->storage->loadMultiple($ids) as $entity) {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entity->uuid(),
          'subid' => NULL,
        ];
      }
    }

    return $entities;
  }

}
