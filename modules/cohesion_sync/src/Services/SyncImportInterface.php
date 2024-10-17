<?php

namespace Drupal\cohesion_sync\Services;

use Drupal\Core\Config\StorageComparerInterface;

/**
 * Interface for SyncImport service.
 *
 * @package Drupal\cohesion_sync\Services
 */
interface SyncImportInterface {

  const CONFIG_PREFIX = 'cohesion_';

  const COMPLETE_REBUILD = [
    'base_unit_settings' => 'cohesion_website_settings.cohesion_website_settings.base_unit_settings',
    'responsive_grid_settings' => 'cohesion_website_settings.cohesion_website_settings.responsive_grid_settings',
  ];

  const ENTITY_WITH_DEPENDENCY = [
    'cohesion_scss_variable' => 'cohesion_website_settings.cohesion_scss_variable.',
    'cohesion_style_guide' => 'cohesion_style_guide.cohesion_style_guide.',
    'cohesion_color' => 'cohesion_website_settings.cohesion_color.',
    'cohesion_font_stack' => 'cohesion_website_settings.cohesion_font_stack.',
  ];

  /*
   * Entity types excluded from rebuild process.
   */
  const EXCLUDES = [
    'cohesion_sync_package',
    'cohesion_component_category',
    'cohesion_helper_category',
  ];

  /**
   * Checks if imported config requires full rebuild.
   *
   * @param array $change_list
   *   List of imported config names.
   *
   * @return bool
   *   True if full rebuild required.
   */
  public function needsCompleteRebuild(array $change_list): bool;

  /**
   * Builds single-dimensional array of changes out of multi-dimensional array.
   *
   * @param array $changes
   *   Multi-dimensional array of changes, outer keys are CRUD Operations.
   *
   * @return array
   *   Flattened array of changed config names.
   */
  public function buildChangeList(array $changes): array;

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
  public function findAffectedEntities(array $change_list, StorageComparerInterface $storageComparer): array;

}
