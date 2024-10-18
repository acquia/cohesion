<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\cohesion_elements\Entity\ComponentTag;
use Drupal\Core\Entity\EntityInterface;

/**
 * Element tag usage base plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
abstract class ElementTagUsageBase extends UsagePluginBase {

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
      if ($entry['type'] == 'tag_id') {
        foreach (ComponentTag::loadMultiple($entry['id']) as $entity) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
