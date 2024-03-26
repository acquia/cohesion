<?php

namespace Drupal\cohesion_sync;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Sync plugin interface.
 *
 * @package Drupal\cohesion_sync
 */
interface SyncPluginInterface extends PluginInspectionInterface {

  /**
   * Return the name of the reusable form plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * The entity interface this plugin works for ($entity implements xxxx).
   *
   * @return mixed
   */
  public function getInterface();

  /**
   * Get the entity an array that can be serialized into Yaml or JSON for
   * transport.
   *
   * @param $entity
   *
   * @return mixed
   */
  public function buildExport($entity);

  /**
   * Get the entity dependencies. Content entity plugins should return [];.
   *
   * @param $entity
   *
   * @return mixed
   */
  public function getDependencies($entity);

  /**
   * Can the package be applied without any user input.
   *
   * @param $package
   *
   * @return mixed
   *
   * @throws \Exception
   */
  public function validatePackageEntryShouldApply($package);

  /**
   * Given a package entry, return a readable label.
   *
   * @param $entry
   * @param $action_state
   * @param $type
   *
   * @return mixed
   */
  public function getActionData($entry, $action_state, $type);

  /**
   * Apply to the site.
   *
   * @param $entry
   *
   * @return mixed
   */
  public function applyPackageEntry($entry);

}
