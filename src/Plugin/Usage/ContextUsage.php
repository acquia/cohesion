<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for context usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "context",
 *   name = @Translation("Context usage"),
 *   entity_type = "context",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class ContextUsage extends UsagePluginBase {

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

    foreach ($data as $entry) {
      if ($entry['type'] == 'entity_view_mode') {
        if ($entity_view_mode = $this->storage->load($entry['value'])) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $entity_view_mode->uuid(),
            // 'subid' => NULL,
            // 'url' => $view_entity->toUrl('edit-form')
          ];
        }
      }
    }

    return $entities;
  }

}
