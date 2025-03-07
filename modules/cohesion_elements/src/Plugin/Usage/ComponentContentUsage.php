<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityInterface;

/**
 * Component content usage plugin.
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
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class ComponentContentUsage extends UsagePluginBase {

  /**
   * @var array
   */
  private $entities;

  /**
   * @param $array
   * @return void
   */
  private function scanForComponentContent($array) {
    foreach ($array as $key => $item) {
      if ($key === 'entity' && isset($item['entityType']) && $item['entityType'] === 'component_content' && isset($item['entityUUID'])) {
        $this->entities[] = [
          'type' => 'component_content',
          'uuid' => $item['entityUUID'],
          'subid' => NULL,
        ];
      }

      if (is_array($item)) {
        // Recurse...
        $this->scanForComponentContent($item);
      }
    }
  }

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
    $this->entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] === 'json_string' && isset($entry['decoded']['canvas'])) {
        // Search for components within the decoded layout canvas.
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($entry['decoded']['canvas']));
        foreach ($iterator as $k => $v) {
          if ($k === 'componentContentId' && $v != NULL) {
            $v = str_replace('cc_', '', $v);
            // Load the component to get its UUID.
            if ($component_content_uuid = $this->loadComponentContent($v)) {
              $this->entities[] = [
                'type' => $this->getEntityType(),
                'uuid' => $component_content_uuid,
                'subid' => NULL,
              ];
            }
          }
        }
      }

      if ($entry['type'] === 'json_string' && isset($entry['decoded']['model'])) {
        $this->scanForComponentContent($entry['decoded']['model']);
      }
    }

    return $this->entities;
  }

  /**
   * @param $v
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed
   */
  public function loadComponentContent($v) {
    $component_entity = $this->storage->loadByProperties(['uuid' => $v]);
    $component_content_entity = reset($component_entity);

    if ($component_content_entity) {
      return $component_content_entity->uuid();
    }
    return FALSE;
  }

}
