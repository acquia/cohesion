<?php

namespace Drupal\sitestudio_data_transformers\Services;

use Drupal\sitestudio_data_transformers\Handlers\ComponentLevel\ComponentLevelHandlerInterface;

/**
 * LayoutCanvasManager Interface - declares public methods.
 */
interface LayoutCanvasManagerInterface {

  /**
   * Service collector callback for Component Handlers.
   *
   * @param \Drupal\sitestudio_data_transformers\Handlers\ComponentLevel\ComponentLevelHandlerInterface $handler
   *   Component handler.
   *
   * @return $this
   */
  public function addHandler(ComponentLevelHandlerInterface $handler): self;

  /**
   * @param string $fieldType
   * @return bool
   */
  public function hasHandlerForType(string $fieldType): bool;

  /**
   * Fetches Component Level Handler for type.
   *
   * @param string $type
   *   Type id.
   * @return bool
   */
  public function getHandlerForType(string $type): ComponentLevelHandlerInterface;

  /**
   * @param $json
   * @return mixed
   */
  public function transformLayoutCanvasJson($json): array;

}
