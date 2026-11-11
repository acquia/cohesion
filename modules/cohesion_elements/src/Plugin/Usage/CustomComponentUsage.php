<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Component usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 *
 * @Usage(
 *   id = "cohesion_component_usage",
 *   name = @Translation("Component usage"),
 *   entity_type = "cohesion_component",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = "category",
 *   group_key_entity_type = "cohesion_component_category",
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class CustomComponentUsage extends ComponentUsage {

  /**
   * Entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The StreamWrapperManager service.
   *
   * @var \Drupal\Core\StreamWrapper\StreamWrapperManager
   */
  protected $streamWrapperManager;

  /**
   * The entity storage for the entity type this plugin works for.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Custom Components service.
   *
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponents;

  /**
   * @param array $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponents
   */
  public function __construct(
    $configuration,
    $plugin_id,
    $plugin_definition,
    EntityTypeManagerInterface $entity_type_manager,
    StreamWrapperManager $stream_wrapper_manager,
    Connection $connection,
    CustomComponentsService $customComponents,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $stream_wrapper_manager, $connection);
    $this->customComponents = $customComponents;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('stream_wrapper_manager'),
      $container->get('database'),
      $container->get('custom.components'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = parent::getScannableData($entity);

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
            // Load the custom component.
            if ($component_entity = $this->customComponents->getComponent($v)) {
              $entities[] = [
                'type' => 'cohesion_component',
                'uuid' => $component_entity['machine_name'],
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
