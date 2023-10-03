<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

/**
 *
 */
interface ComponentLevelHandlerInterface {

  /**
   * Returns "type" - component, custom component, coomponent content.
   *
   * @return string
   */
  public static function type(): string;

  /**
   * @return mixed
   */
  public function getSchema();

  /**
   * @return mixed
   */
  public function hasChildren();

  /**
   * @return mixed
   */
  public function getChildren();

}
