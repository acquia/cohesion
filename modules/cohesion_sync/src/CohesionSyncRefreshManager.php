<?php

namespace Drupal\cohesion_sync;

use Drupal\Core\Config\Entity\ConfigEntityType;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\cohesion\UsagePluginManager;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\State\StateInterface;

/**
 * Class CohesionSyncRefreshManager.
 *
 * @package Drupal\cohesion_sync
 */
class CohesionSyncRefreshManager {
  use DependencySerializationTrait;

  const PACKAGE_REQUIREMENTS = 'package_requirements';
  const PACKAGE_CONTENTS = 'package_content';
  const PACKAGE_EXCLUDE_ENTITY_TYPES = 'exclude_entity_types';

  const COHESION_SYNC_REFRESH_REQUEST = 'cohesion_sync.refresh_request';

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * The state key/value store.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * CohesionSyncRefreshManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\cohesion\UsagePluginManager $usagePluginManager
   * @param \Drupal\Core\State\StateInterface $state
   *   The state key/value store.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager,
                              UsagePluginManager $usagePluginManager,
                              StateInterface $state) {
    $this->entityTypeManager = $entityTypeManager;
    $this->usagePluginManager = $usagePluginManager;
    $this->state = $state;
  }

  /**
   * Helper function to set Request.
   */
  public function setRequestSettings(Request $request) {
    $this->state->set(self::COHESION_SYNC_REFRESH_REQUEST, $request->getContent());
  }

  /**
   * Helper function to get Request.
   */
  public function getRequestSettings() {
    $settings = $this->state->get(self::COHESION_SYNC_REFRESH_REQUEST, FALSE);
    if ($settings) {
      $settings = json_decode($settings, TRUE);
    }

    return $settings;
  }

  /**
   * Given a Usage plugin definition and an entity, get the group name. Used
   * in the LHS panel form() to organize the entities.
   *
   * @param $usage
   * @param $entity
   * @param $all_suffix
   *
   * @return string
   */
  public function getGroupLabelFromUsage($usage, $entity, $all_suffix) {
    $group_key_entity_type = explode(',', $usage['group_key_entity_type']);

    // Get the group label.
    if ($usage['group_key']) {
      $label = [];

      foreach (explode(',', $usage['group_key']) as $index => $group_key) {
        // The group is the string of the group key value.
        if (is_bool($usage['group_key_entity_type']) || (is_array($usage['group_key_entity_type']) && !$usage['group_key_entity_type'][$index])) {
          // Probably a content template.
          if ($group_key) {
            $label[] = $entity->get($group_key);
          }
          else {
            $label[] = 'All';
          }
        }
        // If the group label is an entity reference get the label from the entity type definition.
        else {
          try {
            $label[] = 'All ' . $this->entityTypeManager->getStorage($group_key_entity_type[$index])->load($entity->get($group_key))->label();
          }
          catch (\Throwable $e) {
            return 'All';
          }
        }
      }

      return implode(' » ', $label);
    }
    else {
      return 'All';
    }
  }

  /**
   * Create a list of entity types with definitions for the LHS requirements panel.
   * See: form().
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getGroupsList() {
    $list = [];

    $definitions = $this->usagePluginManager->getDefinitions();

    foreach ($definitions as $usage_plugin_definition) {
      // Only include entity types that are ste to appear in this list (as defined in their Usage plugin).
      if (!$usage_plugin_definition['exclude_from_package_requirements']) {
        // Get the entity type definition.
        try {
          $entity_type_definition = $this->entityTypeManager->getDefinition($usage_plugin_definition['entity_type']);
        }
        catch (\Throwable $e) {
          continue;
        }

        // Only include config entities.
        if ($entity_type_definition instanceof ConfigEntityType) {
          $list[$entity_type_definition->getPluralLabel()->__toString()] = [
            'usage' => $usage_plugin_definition,
            'entity_type' => $entity_type_definition,
            'storage' => $this->entityTypeManager->getStorage($usage_plugin_definition['entity_type']),
          ];

        }
      }
    }

    ksort($list);
    return $list;
  }

}
