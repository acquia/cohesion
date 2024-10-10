<?php

namespace Drupal\cohesion;

/**
 * Class EntityUpdateManager.
 *
 * Whenever a DX8 entity is saved, this service checks the entity to see if any
 * of the EntityUpdate plugins need to be run. If they are, the entity JSON is
 * updated and the name of the latest EntityUpdate plugin is added to the
 * entity so they are only run once.
 *
 * New update callbacks shoud be defined as new EntityUpdate plugins (see
 * /src/Plugin/EntityUpdate/*)
 *
 * @package Drupal\cohesion
 */
class EntityUpdateManager {

  /**
   * @var \Drupal\cohesion\EntityUpdatePluginManager
   */
  protected $updatePluginManager;

  /**
   * @var string
   */
  protected $latestPluginId;

  /**
   * EntityUpdateManager constructor.
   *
   * @param \Drupal\cohesion\EntityUpdatePluginManager $update_plugin_manager
   */
  public function __construct(EntityUpdatePluginManager $update_plugin_manager) {
    $this->updatePluginManager = $update_plugin_manager;

    // Stash the latest plugin ID available on this site.
    $all_definitions = $this->getAllPluginDefinitions();

    if ($plugin_definition = end($all_definitions)) {
      $this->latestPluginId = $plugin_definition['id'];
    }
    else {
      return FALSE;
    }
  }

  /**
   *
   */
  public function entityNeedUpdate(EntityUpdateInterface &$entity) {

    $all_plugins = $this->getAllPluginDefinitions();
    if ($entity->isNew() || is_null($entity->getLastAppliedUpdate()) || (isset($all_plugins[$entity->getLastAppliedUpdate()]) && end($all_plugins) != $all_plugins[$entity->getLastAppliedUpdate()])) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Entrypoint.
   *
   * This should check 'last_entity_update' and if it's blank (a new entity)
   * set the latest available update so nothing gets run for this entity.
   *
   * @param \Drupal\cohesion\EntityUpdateInterface $entity
   *
   * @return array
   */
  public function apply(EntityUpdateInterface &$entity) {
    $applied = [];

    // Get a sorted list of available updates.
    $all_plugins = $this->getAllPluginDefinitions();

    if (!$entity->isNew() || (!is_null($entity->getLastAppliedUpdate()) && isset($all_plugins[$entity->getLastAppliedUpdate()]))) {
      if (isset($all_plugins[$entity->getLastAppliedUpdate()])) {
        $start_index = $entity->getLastAppliedUpdate() ? (int) str_replace('entityupdate_', '', $entity->getLastAppliedUpdate()) : 0;
      }
      else {
        // Couldn't find the class method defined in the entity.
        $start_index = 0;
      }

      foreach ($all_plugins as $plugin_id => $definition) {
        $plugin_index = (int) str_replace('entityupdate_', '', $plugin_id);
        if ($start_index < $plugin_index) {

          // Get and instance of this update plugin.
          if ($instance = $this->getPluginInstance($plugin_id)) {
            // Run the update callback.
            if ($instance->runUpdate($entity)) {
              $entity->setLastAppliedUpdate($plugin_id);
            }

            // For unit tests.
            $applied[] = $plugin_id;
          }
        }
      }
    }
    // New entity.
    else {
      // Set this entity to the latest update (so no updates ever get applied).
      if ($this->latestPluginId) {
        $entity->setLastAppliedUpdate($this->latestPluginId);
      }
    }

    // See EntityupdateManagerUnitTest.
    return $applied;
  }

  /**
   * Is the supplied entityupdate_xxxx is higher than available on this site?
   *
   * @param $id
   *
   * @return bool
   */
  public function pluginIdInRange($id) {
    $test_id = str_replace('entityupdate_', '', $id);
    $site_id = str_replace('entityupdate_', '', $this->latestPluginId);

    return $test_id <= $site_id;
  }

  /**
   * Gets a sorted list of all update plugins.
   *
   * @return array
   */
  public function getAllPluginDefinitions() {
    $definitions = $this->updatePluginManager->getDefinitions();
    ksort($definitions);
    return $definitions;
  }

  /**
   * Get an instance of the plugin by ID.
   *
   * @param $plugin_id
   *
   * @return bool|object
   */
  public function getPluginInstance($plugin_id) {
    try {
      return $this->updatePluginManager->createInstance($plugin_id);
    }
    catch (\Throwable $e) {
      return FALSE;
    }
  }

  /**
   *
   */
  public function getLastPluginId() {
    return $this->latestPluginId;
  }

}
