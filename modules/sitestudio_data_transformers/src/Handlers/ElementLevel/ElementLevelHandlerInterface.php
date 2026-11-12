<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;

/**
 * Element level handlers interface - describes common methods.
 */
interface ElementLevelHandlerInterface {

  /**
   * Returns Site Studio element type id.
   *
   * @return string
   */
  public function id(): string;

  /**
   * Returns element data.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $element
   *   Site Studio element.
   *
   * @return array
   *  Element data.
   */
  public function getData(Element $element, ElementModel $elementModel): array;

  /**
   * Builds schema at element level.
   * @return array
   */
  public function getStaticSchema(): array;

}
