<?php

namespace Drupal\cohesion_sync\Services;

use Drupal\cohesion\UsageUpdateManager;
use Drupal\Core\Config\StorageComparerInterface;

/**
 * Service to handle change list and rebuild during site studio sync import.
 *
 * @package Drupal\cohesion_sync\Services
 */
class SyncImport implements SyncImportInterface {

  /**
   * UsageUpdateManager service.
   *
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * SyncImport service constructor.
   *
   * @param \Drupal\cohesion\UsageUpdateManager $usageUpdateManager
   *   Update manager service.
   */
  public function __construct(UsageUpdateManager $usageUpdateManager) {
    $this->usageUpdateManager = $usageUpdateManager;
  }

  /**
   * Checks if imported config requires full rebuild.
   *
   * @param array $change_list
   *   List of imported config names.
   *
   * @return bool
   *   True if full rebuild required.
   */
  public function needsCompleteRebuild(array $change_list): bool {
    foreach ($change_list as $name) {
      if (in_array($name, self::COMPLETE_REBUILD)) {
        return TRUE;
      }
    }

    return FALSE;
  }

  /**
   * Builds single-dimensional array of changes out of multi-dimensional array.
   *
   * @param array $changes
   *   Multi-dimensional array of changes, outer keys are CRUD Operations.
   *
   * @return array
   *   Flattened array of changed config names.
   */
  public function buildChangeList(array $changes): array {
    $change_list = [];
    foreach ($changes['create'] as $name) {
      if ($name) {
        $change_list[] = $name;
      }
    }
    foreach ($changes['update'] as $name) {
      if ($name) {
        $change_list[] = $name;
      }
    }

    return $change_list;
  }

  /**
   * Finds entities affected by config import and returns an array.
   *
   * @param array $change_list
   *   Array of imported config names.
   * @param \Drupal\Core\Config\StorageComparerInterface $storageComparer
   *   Storage comparer service.
   *
   * @return array
   *   List of entities that need rebuild.
   */
  public function findAffectedEntities(array $change_list, StorageComparerInterface $storageComparer): array {
    $rebuild_list = [];

    foreach ($change_list as $name) {
      if (str_starts_with($name, self::CONFIG_PREFIX)) {
        $uuid = $storageComparer->getSourceStorage()->read($name)['uuid'];
        $type = explode('.', $name)[1];
        // Skip entities not meant to be rebuilt.
        if (in_array($type, self::EXCLUDES)) {
          continue;
        }
        $rebuild_list[$uuid] = $type;

        foreach (self::ENTITY_WITH_DEPENDENCY as $entity_type) {
          $target_entity = $storageComparer->getTargetStorage()->read($name);
          if ($target_entity !== FALSE && str_starts_with($name, $entity_type)) {
            $dependant_entities = $this->usageUpdateManager->getInUseEntitiesListByUuid($target_entity['uuid']);
            if (!empty($dependant_entities)) {
              $rebuild_list = array_merge($rebuild_list, $dependant_entities);
            }
          }
        }
      }
    }

    return $rebuild_list;
  }

}
