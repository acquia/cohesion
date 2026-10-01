<?php

namespace Drupal\cohesion;

use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for grouped entity plugin.
 *
 * @package Drupal\cohesion
 */
interface EntityGroupsPluginInterface extends PluginInspectionInterface {

  /**
   * Return the name of the reusable form plugin.
   *
   * @return string
   */
  public function getName();

  /**
   * Build the JSON from multiple entities.
   *
   * @return mixed
   */
  public function getGroupJsonValues();

  /**
   * Given a JSON model, save back out a group of entities of this type.
   *
   * @param $decoded_json
   *
   * @return mixed
   */
  public function saveFromModel($decoded_json);

}
