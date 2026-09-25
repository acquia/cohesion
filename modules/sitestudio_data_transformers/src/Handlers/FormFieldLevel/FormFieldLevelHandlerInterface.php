<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;

/**
 * Form Field level handlers interface - describes common methods.
 */
interface FormFieldLevelHandlerInterface {

  /**
   * Returns Site Studio element type id.
   *
   * @return string
   */
  public function id(): string;

  /**
   * Returns form field element data.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $formField
   *   Site Studio form field element.
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $elementModel
   *   Element model from loaded component instance.
   *
   * @return array
   *   Form element data.
   */
  public function getData(Element $formField, ElementModel $elementModel): array;

  /**
   * Builds schema at form field level.
   * @return array
   */
  public function getStaticSchema(): array;

}
