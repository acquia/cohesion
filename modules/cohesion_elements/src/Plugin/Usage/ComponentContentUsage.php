<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\Core\Entity\EntityInterface;
use Drupal\cohesion\UsagePluginBase;
use Drupal\Component\Serialization\Json;

/**
 * Class ComponentContentUsage.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 *
 * @Usage(
 *   id = "cohesion_component_content_usage",
 *   name = @Translation("Component content usage"),
 *   entity_type = "component_content",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = "category",
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "site_studio",
 *   scan_groups = {"core", "site_studio"}
 * )
 */
class ComponentContentUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Get the json values list.
    try {
      $cohesion_layout_entity = $entity->get('layout_canvas')->entity;
    }
    catch (\Exception $e) {
      return $scannable;
    }

    // Build the array for the plugin manager.
    if ($json_values = $cohesion_layout_entity->get('json_values')->getValue()) {
      // Pop the first entity off the array.
      if (count($json_values)) {
        $json_values = reset($json_values)['value'];
      }

      $scannable[] = [
        'type' => 'json_string',
        'value' => $json_values,
        'decoded' => Json::decode($json_values),
      ];
    }

    // Return everything.
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
          if ($k == 'componentContentId' && $v != NULL) {
            $v = str_replace('cc_', '', $v);
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
