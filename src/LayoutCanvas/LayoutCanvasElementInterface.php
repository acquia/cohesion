<?php

namespace Drupal\cohesion\LayoutCanvas;

/**
 * Interface LayoutCanvasElementInterface.
 *
 * @package Drupal\cohesion\LayoutCanvas
 */
interface LayoutCanvasElementInterface {

  /**
   * @param $is_preview
   *
   * @return mixed
   */
  public function prepareDataForAPI($is_preview);

}
