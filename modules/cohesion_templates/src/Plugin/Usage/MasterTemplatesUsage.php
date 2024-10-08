<?php

namespace Drupal\cohesion_templates\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * master templates usage plugin.
 *
 * @package Drupal\cohesion_templates\Plugin\Usage
 *
 * @Usage(
 *   id = "master_templates_usage",
 *   name = @Translation("Master templates usage"),
 *   entity_type = "cohesion_master_templates",
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
class MasterTemplatesUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    // The JSON values and the master template.
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
      if ($entry['type'] == 'entity_id' && $entry['entity_type'] == 'cohesion_master_templates' && isset($entry['id']) && $entry['id'] !== NULL) {

        // If it's set as default, get the default master template id.
        if ($entry['id'] == '__none__') {

          $master_template_id = $this->storage->getQuery('AND')
            ->accessCheck(TRUE)
            ->condition('default', TRUE)
            ->execute();

          if ($master_template_id) {
            $master_template_id = array_shift($master_template_id);
          }
          else {
            continue;
          }

        }
        // If it's not, just pass through the set master template.
        else {
          $master_template_id = $entry['id'];
        }

        // Attempt to load the entity.
        if ($content_template_entity = $this->storage->load($master_template_id)) {
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
