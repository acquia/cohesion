<?php

namespace Drupal\sitestudio_data_transformers\Services;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementLevelHandlerInterface;

/**
 * ElementManager Interface - declares public methods.
 */
interface ElementManagerInterface {

  /**
   * Checks if a handler is available for specific field type.
   *
   * @param string $fieldType
   *   Site Studio element field type, for example "input".
   *
   * @return bool
   *   True if handler is available.
   */
  public function hasHandlerForType(string $fieldType): bool;

  /**
   * Fetches correct Handler for the Site Studio field type.
   *
   * @param string $fieldType
   *   Site Studio element field type, for example "input".
   *
   * @return \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementLevelHandlerInterface
   *   Data and Schema handler for this field type.
   */
  public function getHandlerForType(string $fieldType): ElementLevelHandlerInterface;

}
