<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for node usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "node_usage",
 *   name = @Translation("Node usage"),
 *   entity_type = "node",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class NodeUsage extends FieldableContentEntityUsageBase {

  /**
   * Custom Components service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponents;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $entity_type_manager, $stream_wrapper_manager, $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $stream_wrapper_manager, $connection);
    $this->customComponents = \Drupal::service('custom.components');
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    // Also scan for custom components in layout canvas data
    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' && isset($entry['decoded']['canvas'])) {
        $this->scanCanvasForCustomComponents($entry['decoded']['canvas'], $entities);
      }
    }

    return $entities;
  }

  /**
   * Scan canvas data for custom components.
   *
   * @param array $canvas
   *   The canvas data to scan.
   * @param array &$entities
   *   The entities array to populate.
   */
  protected function scanCanvasForCustomComponents(array $canvas, array &$entities) {
    // Use a more efficient approach to scan for custom components
    $this->scanArrayRecursively($canvas, $entities);
  }

  /**
   * Recursively scan an array for custom components.
   *
   * @param array $data
   *   The data to scan.
   * @param array &$entities
   *   The entities array to populate.
   */
  protected function scanArrayRecursively(array $data, array &$entities) {
    foreach ($data as $key => $value) {
      if ($key === 'componentId' && is_string($value) && !empty($value)) {
        // Check if this is a custom component
        if ($component_entity = $this->customComponents->getComponent($value)) {
          $entities[] = [
            'type' => 'cohesion_component',
            'uuid' => $component_entity['machine_name'],
            'subid' => NULL,
          ];
        }
      }
      elseif (is_array($value)) {
        $this->scanArrayRecursively($value, $entities);
      }
    }
  }

}
