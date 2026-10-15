<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Element category usage base plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
abstract class ElementCategoryUsageBase extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'category_id') {
        if ($component_entity = $this->storage->load($entry['id'])) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $component_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
