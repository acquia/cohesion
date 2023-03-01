<?php

namespace Drupal\cohesion_templates\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Content templates usage plugin.
 *
 * @package Drupal\cohesion_templates\Plugin\Usage
 *
 * @Usage(
 *   id = "content_templates_usage",
 *   name = @Translation("Content templates usage"),
 *   entity_type = "cohesion_content_templates",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = "entity_type,bundle",
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class ContentTemplatesUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    /** @var \Drupal\cohesion_templates\Entity\ContentTemplates $entity */

    // Get scannable variables from the entity.
    return [
      [
        'type' => 'entity_id',
        'entity_type' => 'cohesion_master_templates',
        'id' => $entity->getMasterTemplate(),
      ],
      [
        'type' => 'json_string',
        'value' => $entity->getJsonValues(),
        'decoded' => $entity->getDecodedJsonValues(),
      ],
      [
        'type' => 'content_entity_type',
        'value' => $entity->get('entity_type'),
      ],
      [
        'type' => 'content_bundle',
        'value' => $entity->get('bundle'),
      ],
      [
        'type' => 'entity_view_mode',
        'value' => $entity->get('entity_type') . '.' . $entity->getViewMode(),
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
      if ($entry['type'] == 'entity_id' && $entry['entity_type'] == 'cohesion_content_templates' && isset($entry['id']) && $entry['id'] !== NULL) {
        // Attempt to load the entity.
        if ($content_template_entity = $this->storage->load($entry['id'])) {
          // Add to the list of dependencies.
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $content_template_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
