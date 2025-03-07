<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Element usage base plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
abstract class ElementUsageBase extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // The JSON values that make up the component.
    $scannable[] = [
      'type' => 'json_string',
      'value' => $entity->getJsonValues(),
      'decoded' => $entity->getDecodedJsonValues(),
    ];

    // Preview image.
    if (!is_array($entity->get('preview_image')) && !empty($entity->get('preview_image'))) {
      $scannable[] = [
        'type' => 'string',
        'value' => $entity->get('preview_image'),
      ];
    }

    // Category.
    if ($category_id = $entity->getCategory()) {
      $scannable[] = [
        'type' => 'category_id',
        'id' => $category_id,
      ];
    }

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' && isset($entry['decoded']['canvas'])) {
        // Search for components within the decoded layout canvas.
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($entry['decoded']['canvas']));
        foreach ($iterator as $k => $v) {
          if ($k == 'componentId' && $v != NULL) {
            // Load the component to get its UUID.
            if ($component_entity = $this->storage->load($v)) {
              $entities[] = [
                'type' => $this->getEntityType(),
                'uuid' => $component_entity->uuid(),
                'subid' => NULL,
              ];
            }
          }
        }
      }
    }

    return $entities;
  }

}
