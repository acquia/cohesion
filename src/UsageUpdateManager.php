<?php

namespace Drupal\cohesion;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class UsageUpdateManager.
 *
 * This service is what the various entities call on their postSave or update,
 * delete or create hooks.
 * It scan itself for instances of various entities and updates the
 * usage_requires table.
 *
 * @package Drupal\cohesion
 */
class UsageUpdateManager {

  /**
   * Holds the usage plugin manager service.
   *
   * @var \Drupal\cohesion\UsagePluginManager
   */
  protected $usagePluginManager;

  /**
   * Holds the database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Holds the entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * Holds the entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * UsageUpdateManager constructor.
   *
   * @param \Drupal\cohesion\UsagePluginManager $usagePluginManager
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(UsagePluginManager $usagePluginManager, Connection $connection, EntityRepository $entityRepository, EntityTypeManagerInterface $entityTypeManager) {
    $this->usagePluginManager = $usagePluginManager;
    $this->connection = $connection;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Get the usage plugin instance for a particular entity (by type).
   *
   * @param $entity
   *
   * @return bool|null|object
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getPluginInstanceForEntity($entity) {
    $instance = NULL;

    // And then the more specific entity def check.
    foreach ($this->usagePluginManager->getDefinitions() as $id => $definition) {
      // Found a plugin that matches the entity type being updated.
      if ($entity && $entity->getEntityTypeId() == $definition['entity_type']) {
        $instance = $this->usagePluginManager->createInstance($id);
        break;
      }
    }

    // Create an instance of the plugin so we can work with it.
    if ($instance) {
      return $instance;
    }

    return FALSE;
  }

  /**
   * Given an entity, what are its dependencies?
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  private function getDependencies(EntityInterface $entity) {
    $scannable_data = [];
    $dependencies = [];
    $scan_groups = [];

    // Find the plugin that controls this plugin type.
    try {
      if ($instance = $this->getPluginInstanceForEntity($entity)) {
        // Get the scannable data (usually JSON for entities).
        $scannable_data = $instance->getScannableData($entity);
        $scan_groups = $instance->getPluginDefinition()['scan_groups'];
      }
    }
    catch (\Throwable $e) {
      return [];
    }

    // Now loop through all the plugins and find dependencies within this data.
    if (!empty($scannable_data)) {
      foreach ($this->usagePluginManager->getDefinitions() as $id => $definition) {
        if (!empty(array_intersect($scan_groups, $definition['scan_groups']))) {
          // Check to see if we can scan for nested types (components in
          // components for example).
          if ($entity->getEntityTypeId() == $definition['entity_type'] && !$definition['scan_same_type']) {
            continue;
          }

          // Send the scannable data to this plugin and return entities.
          if ($instance = $this->usagePluginManager->createInstance($id)) {
            // Only process if the entity defined in the Usage plugin exists.
            if ($instance->getStorage()) {
              // Search for usages of this type of entity within the entity
              // currently being saved.
              $dependencies = array_merge($dependencies, $instance->scanForInstancesOfThisType($scannable_data, $entity));
            }
          }
        }
      }

      // Unique the arrays and merge the usages into the database.
      $dependencies = array_unique($dependencies, SORT_REGULAR);
    }

    return $dependencies;
  }

  /**
   * Update the cohesion_usage table with an entities dependencies.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return int
   *
   * @throws \Exception
   */
  public function buildRequires(EntityInterface $entity) {
    // Get the dependencies.
    $dependencies = $this->getDependencies($entity);

    // Remove existing dependencies.
    $this->connection->delete('coh_usage')->condition('source_uuid', $entity->uuid())->execute();

    // Add the dependencies.
    foreach ($dependencies as $dependency) {
      if ($dependency['uuid']) {
        // Remove duplicate.
        $this->connection->delete('coh_usage')
          ->condition('source_uuid', $entity->uuid())
          ->condition('requires_uuid', $dependency['uuid'])
          ->execute();

        if ($dependency['type']) {
          // Insert.
          $this->connection->insert('coh_usage')->fields([
            'source_uuid' => $entity->uuid(),
            'source_type' => $entity->getEntityTypeId(),
            'requires_uuid' => $dependency['uuid'],
            'requires_type' => $dependency['type'],
          ])->execute();
        }
      }
    }

    return count($dependencies);
  }

  /**
   * Remove an entity from the usage table (left and right hand side) and any
   * core file usage.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function removeUsage(EntityInterface $entity) {
    try {
      // Loop through all the entities that this entity requires.
      $requires = $this->connection->select('coh_usage', 'c1')
        ->fields('c1', ['requires_uuid', 'requires_type'])
        ->condition('c1.source_uuid', $entity->uuid(), '=')
        ->execute()
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      return FALSE;
    }

    // For each entity that this requires, run the plugin specific removal function.
    foreach ($requires as $uuid => $type) {
      try {
        $requires_entity = $this->entityRepository->loadEntityByUuid($type, $uuid);
      }
      catch (\Exception $e) {
        continue;
      }

      if ($instance = $this->getPluginInstanceForEntity($requires_entity)) {
        if (method_exists($instance, 'removeUsage')) {
          $instance->removeUsage($requires_entity, $entity);
        }
      }
    }

    // And delete from the usage table.
    try {
      $query = $this->connection->delete('coh_usage')->condition('source_uuid', $entity->uuid(), '=');

      $query->execute();
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Get the raw list of in-use entities.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   */
  public function getInUseEntitiesList(EntityInterface $entity) {
    // Get the usage from the table (right hand side lookup).
    try {
      $usage = $this->connection->select('coh_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.requires_uuid', $entity->uuid(), '=')
        ->execute()
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      // DB connection problem.
      return [];
    }

    return $usage;
  }

  /**
   * Is the entity is use?
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool
   */
  public function hasInUse(EntityInterface $entity) {
    // Get the usage from the table (right hand side lookup).
    try {
      $query = $this->connection->select('coh_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.requires_uuid', $entity->uuid(), '=');

      $usage = $query->countQuery()->execute()->fetchField();

    }
    catch (\Exception $e) {
      // DB connection problem.
      return FALSE;
    }

    return $usage > 0;
  }

  /**
   * The in-use modal calls this to get a grouped and formatted list of
   * dependencies.
   * This function is potentially expensive to perform.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getFormattedInUseEntitiesList(EntityInterface $entity) {
    $grouped = [];
    $usage = $this->getInUseEntitiesList($entity);

    // Build the grouped list.
    foreach ($usage as $source_uuid => $source_type) {
      // Load the entity so we can get the title and url.
      /** @var \Drupal\Core\Entity\EntityInterface $entity */
      $entity = $this->entityRepository->loadEntityByUuid($source_type, $source_uuid);

      if ($entity) {
        // Get the edit URL.
        try {
          $entity_edit_url = $entity->toUrl('edit-form')->toString();
        }
        catch (\Exception $e) {
          $entity_edit_url = FALSE;
        }

        // Get the group label (from entity type).
        $group_label = $this->entityTypeManager->getDefinition($source_type)
          ->getLabel()
          ->render();

        // Update the grouped list.
        $grouped[$group_label][] = [
          'uuid' => $source_uuid,
          'name' => $entity->label(),
          'url' => $entity_edit_url,
          'entity_type' => $entity->getEntityTypeId(),
        ];
      }
    }

    return $grouped;
  }

  /**
   * Rebuild in-use for a specific entity type.
   *
   * @refactor - use an entity iterator once this lands:
   *   https://www.drupal.org/project/drupal/issues/2577417
   *
   * @param $type
   */
  public function rebuildEntityType($type) {
    try {
      foreach ($this->entityTypeManager->getStorage($type)->loadMultiple() as $entity) {
        $this->buildRequires($entity);
      }
    }
    catch (\Exception $e) {
    }
  }

  /**
   * Build config entity dependencies from Usage table.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityBase $entity
   *
   * @return array
   */
  public function buildConfigEntityDependencies(ConfigEntityBase $entity) {
    $list = [];

    if ($entity instanceof ConfigEntityBase) {
      // Get the dependencies from the Usage table (left to right lookup).
      try {
        $dependencies = $this->connection->select('coh_usage', 'c1')
          ->fields('c1', ['requires_uuid', 'requires_type'])
          ->condition('c1.source_uuid', $entity->uuid(), '=')
          ->execute()
          ->fetchAllKeyed();
      }
      catch (\Exception $e) {
        // DB connection problem.
        return $list;
      }

      // Loop through the results and add them to the dependencies.
      foreach ($dependencies as $uuid => $type) {
        try {
          /** @var \Drupal\Core\Entity\EntityInterface $dependency_entity */
          if ($dependency_entity = $this->entityRepository->loadEntityByUuid($type, $uuid)) {
            $list[] = [
              'key' => $dependency_entity->getConfigDependencyKey(),
              'type' => $dependency_entity->getEntityTypeId(),
              'id' => $dependency_entity->id(),
              'uuid' => $dependency_entity->uuid(),
            ];
          }

        }
        catch (\Exception $e) {
        }
      }

      return $list;
    }
  }

}
