<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;

/**
 * Handles Site Studio components data and schema.
 */
class ComponentHandler extends ComponentLevelBase {

  /**
   * Site Studio Component "type".
   */
  const TYPE = 'component';

  /**
   * Component regex pattern.
   */
  const PATTERN = '^component$';

  /**
   * Processed form fields uuids.
   *
   * @var array
   */
  protected $processedFields = [];

  /**
   * {@inheritdoc}
   */
  public static function type(): string {
    return self::TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function pattern(): string {
    return self::PATTERN;
  }

  /**
   * {@inheritdoc}
   */
  public function getTransformedJson(Element $component): array {
    $componentConfig = Component::load($component->getComponentID());
    if (!$componentConfig instanceof Component) {
      return [];
    }
    $layoutCanvasInstance = $componentConfig->getLayoutCanvasInstance();
    $fields = [];
    if ($layoutCanvasInstance instanceof LayoutCanvas) {
      $fields = $this->processForm($layoutCanvasInstance, $component->getModel());
    }

    return $this->buildComponentArray($component, $fields);
  }

}
