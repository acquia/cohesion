<?php

namespace Drupal\cohesion;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\file\FileInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

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

  use ExceptionLoggerTrait;
  const FILE_ENTITY_TYPE = 'file';

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
   * Stores Plugin Definitions.
   *
   * @var array|mixed[]|null
   */
  protected $definitions;

  /**
   * Stores Usage Plugin Instances.
   *
   * @var array|mixed[]|null
   */
  protected $instancesByType;

  /**
   * Stores Usage Plugin Instances.
   *
   * @var array|mixed[]|null
   */
  protected $instancesById;

  /**
   * Logger Channel Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerChannelFactory;

  /**
   * UsageUpdateManager constructor.
   *
   * @param \Drupal\cohesion\UsagePluginManager $usagePluginManager
   * @param \Drupal\Core\Database\Connection $connection
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerChannelFactory
   */
  public function __construct(
    UsagePluginManager $usagePluginManager,
    Connection $connection,
    EntityRepository $entityRepository,
    EntityTypeManagerInterface $entityTypeManager,
    LoggerChannelFactoryInterface $loggerChannelFactory,
  ) {
    $this->usagePluginManager = $usagePluginManager;
    $this->connection = $connection;
    $this->entityRepository = $entityRepository;
    $this->entityTypeManager = $entityTypeManager;
    $this->definitions = $usagePluginManager->getDefinitions();
    $this->loggerChannelFactory = $loggerChannelFactory;
  }

  /**
   * Get the usage plugin instance for a particular entity.
   *
   * @param $entity
   *
   * @return bool|null|object
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  public function getPluginInstanceForEntity(EntityInterface $entity) {
    return $this->getPluginInstanceForEntityType($entity->getEntityTypeId());
  }

  /**
   * Get the usage plugin for a particular entity type.
   *
   * @param string $entityType
   *
   * @return mixed|object|null
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getPluginInstanceForEntityType(string $entityType) {
    if (!isset($this->instances[$entityType])) {
      foreach ($this->definitions as $id => $definition) {
        if ($definition['entity_type'] === $entityType) {
          $this->instancesByType[$entityType] = $this->instancesById[$id] = $this->usagePluginManager->createInstance($id);
          break;
        }
      }
    }

    return $this->instancesByType[$entityType] ?? NULL;
  }

  /**
   * Gets the usage plugin for specific ID.
   *
   * @param string $id
   *
   * @return mixed|null
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getPluginInstanceForId(string $id) {
    if (!isset($this->instancesById[$id])) {
      $instance = $this->usagePluginManager->createInstance($id);
      $this->instancesByType[$instance->getEntityType()] = $this->instancesById[$id] = $instance;
    }

    return $this->instancesById[$id] ?? NULL;
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
  public function getDependencies(EntityInterface $entity) {
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
      foreach ($this->definitions as $id => $definition) {
        if (!empty(array_intersect($scan_groups, $definition['scan_groups']))) {
          // Check to see if we can scan for nested types (components in
          // components for example).
          if ($entity->getEntityTypeId() == $definition['entity_type'] && !$definition['scan_same_type']) {
            continue;
          }

          // Send the scannable data to this plugin and return entities.
          if ($instance = $this->getPluginInstanceForId($id)) {
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
        ->condition('c1.source_uuid', $entity->uuid())
        ->condition('c1.requires_type', self::FILE_ENTITY_TYPE)
        ->execute()
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      $this->logException($e);
      return FALSE;
    }

    // Only 'file' entities have special treatment.
    foreach ($requires as $uuid => $type) {
      $this->removeFileUsage($uuid, $entity);
    }

    // And delete from the usage table.
    try {
      $query = $this->connection->delete('coh_usage')->condition('source_uuid', $entity->uuid(), '=');

      $query->execute();
    }
    catch (\Exception $e) {
      $this->logException($e);
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Get the raw list of in-use entities for Entity object.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return {@inheritdoc}
   */
  public function getInUseEntitiesList(EntityInterface $entity) {
    return $this->getInUseEntitiesListByUuid($entity->uuid());
  }

  /**
   * Get the raw list of in-use entities by using Entity UUID.
   *
   * @param string $uuid
   *   Uuid string.
   *
   * @return array
   *   Array of entity types and uuids in ['uuid' => 'entity_type'] format.
   */
  public function getInUseEntitiesListByUuid(string $uuid): array {
    // Get the usage from the table (right hand side lookup).
    try {
      $usage = $this->connection->select('coh_usage', 'c1')
        ->fields('c1', ['source_uuid', 'source_type'])
        ->condition('c1.requires_uuid', $uuid, '=')
        ->execute()
        ->fetchAllKeyed();
    }
    catch (\Exception $e) {
      $this->logException($e);
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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Config Entity.
   *
   * @return array
   *   Array of dependencies.
   */
  public function buildConfigEntityDependencies(EntityInterface $entity) {
    $list = [];
    // Request storage to hold dependencies already processed
    // to avoid processing the same entities multiple times.
    $dep_list = &drupal_static(__FUNCTION__, []);

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

      // Group uuids to be processed by entity type
      // to use loadMultiple instead of loading by UUID one by one
      // for more performance.
      $typed_uuids = [];
      foreach ($dependencies as $uuid => $type) {
        // If the entity has already been processed by another
        // dependency calculation add to the list and skip.
        if (isset($dep_list[$uuid])) {
          $list[] = $dep_list[$uuid];
          continue;
        }
        $typed_uuids[$type][] = $uuid;
      }

      // Loop through the results and add them to the dependencies.
      foreach ($typed_uuids as $type => $uuids) {

        try {
          $entity_type = $this->entityTypeManager->getDefinition($type);
        }
        catch (\Exception $e) {
          \Drupal::logger('cohesion')->warning($e->getMessage());
          continue;
        }
        $ids = $this->entityTypeManager->getStorage($type)->getQuery()
          ->accessCheck(TRUE)
          ->condition($entity_type->getKey('uuid'), $uuids, 'IN')
          ->execute();

        $entities = $this->entityTypeManager->getStorage($type)->loadMultiple($ids);

        foreach ($entities as $dependency_entity) {

          $item = [
            'key' => $dependency_entity->getConfigDependencyKey(),
            'dependency_name' => $dependency_entity->getConfigDependencyName(),
            'type' => $dependency_entity->getEntityTypeId(),
            'id' => $dependency_entity->id(),
            'uuid' => $dependency_entity->uuid(),
          ];
          $list[] = $item;
          // Add to the processed list of entities so we don't do it again
          // if it's a dependency of another entity.
          $dep_list[$dependency_entity->uuid()] = $item;
        }
      }
    }
    return $list;
  }

  /**
   * Handles 'file' entity removal.
   *
   * @var string $uuid
   * @var \Drupal\Core\Entity\EntityInterface $entity
   */
  public function removeFileUsage(string $uuid, EntityInterface $entity): void {
    try {
      $fileEntity = $this->entityRepository->loadEntityByUuid(self::FILE_ENTITY_TYPE, $uuid);
      if ($fileEntity instanceof FileInterface) {
        /** @var \Drupal\cohesion\Plugin\Usage\FileUsage $plugin */
        $plugin = $this->getPluginInstanceForEntityType(self::FILE_ENTITY_TYPE);
        $plugin->removeUsage($fileEntity, $entity);
      }
    }
    catch (\Exception $e) {
      $this->logException($e);
    }
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $source_entity
   * @param array $types - limit the search by types
   *
   * @return array
   */
  public function getEntitiesInUseInSource(EntityInterface $source_entity, $types = []) {
    // Get the usage from the table (right hand side lookup).
    try {
      $query = $this->connection->select('coh_usage', 'c1')
        ->fields('c1', ['requires_uuid', 'requires_type'])
        ->condition('c1.source_uuid', $source_entity->uuid(), '=');

      if (!empty($types)) {
        $query->condition('requires_type', $types, 'IN');
      }

      $usage = $query->execute()->fetchAllKeyed();
    }
    catch (\Exception $e) {
      // DB connection problem.
      $this->loggerChannelFactory->get('cohesion')->error($e->getMessage());
    }

    return $usage;
  }

}
