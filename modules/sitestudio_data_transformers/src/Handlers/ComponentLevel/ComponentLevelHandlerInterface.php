<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;

/**
 *
 */
interface ComponentLevelHandlerInterface {

  /**
   * Returns "type" - component, custom component, component content.
   *
   * @return string
   */
  public static function type(): string;

  /**
   * Transforms Component element into abstract JSON data structure.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $component
   *
   * @return array
   */
  public function getTransformedJson(Element $component): array;

  /**
   * Returns static schema object.
   *
   * @return object
   */
  public function getStaticSchema(): array;

  /**
   * Returns regex pattern of "type" property.
   *
   * @return string
   */
  public function pattern(): string;

}
