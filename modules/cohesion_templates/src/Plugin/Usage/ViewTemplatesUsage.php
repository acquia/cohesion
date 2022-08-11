<?php

namespace Drupal\cohesion_templates\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * View templates usage plugin.
 *
 * @package Drupal\cohesion_templates\Plugin\Usage
 *
 * @Usage(
 *   id = "view_templates_usage",
 *   name = @Translation("View templates usage"),
 *   entity_type = "cohesion_view_templates",
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
class ViewTemplatesUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    // The JSON values from the view template.
    return [
      [
        'type' => 'json_string',
        'value' => $entity->getJsonValues(),
        'decoded' => $entity->getDecodedJsonValues(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      // Content template in a template selector dropdown.
      if ($entry['type'] == 'entity_id' && $entry['entity_type'] == 'cohesion_view_template' && isset($entry['id']) && $entry['id'] !== NULL) {
        // Attempt to load the entity.
        if ($view_template_entity = $this->storage->load($entry['id'])) {
          // Add to the list of dependencies.
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $view_template_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
