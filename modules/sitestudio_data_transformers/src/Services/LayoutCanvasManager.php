<?php

namespace Drupal\sitestudio_data_transformers\Services;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\sitestudio_data_transformers\Handlers\ComponentLevel\ComponentLevelHandlerInterface;

/**
 * Manager service to handle Layout Canvas schema and data.
 */
class LayoutCanvasManager implements LayoutCanvasManagerInterface {

  /**
   * @var \Drupal\sitestudio_data_transformers\Handlers\ComponentLevel\ComponentLevelHandlerInterface[]
   */
  protected $handlers = [];

  /**
   * Schema.
   *
   * @var array
   */
  protected array $schema;

  /**
   * Form Field manager.
   *
   * @var \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface
   */
  protected $formFieldManager;

  /**
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   */
  public function __construct(
    FormFieldManagerInterface $formFieldManager,
  ) {
    $this->formFieldManager = $formFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public function addHandler(ComponentLevelHandlerInterface $handler): self {
    $this->handlers[$handler::type()] = $handler;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasHandlerForType(string $fieldType): bool {
    return array_key_exists($fieldType, $this->handlers);
  }

  /**
   * {@inheritdoc}
   */
  public function getHandlerForType(string $type): ComponentLevelHandlerInterface {
    return $this->handlers[$type];
  }

  /**
   * {@inheritdoc}
   */
  public function transformLayoutCanvasJson($json): array {
    $layoutCanvas = new LayoutCanvas($json);
    $elements = $layoutCanvas->getCanvasElements();
    $layoutCanvasJson = [];
    foreach ($elements as $element) {
      if ($element->isComponent() && $element->getModel()) {
        $componentData = $this->processComponent($element);
        if (!empty($componentData)) {
          $layoutCanvasJson[] = $componentData;
        }
      }
    }

    return $layoutCanvasJson;
  }

  /**
   * Processes Site Studio components.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $component
   *   Site Studio component object.
   *
   * @return array
   *   Returns processed component or empty array.
   */
  protected function processComponent(Element $component): array {
    $data = [];
    $type = $this->getComponentType($component);
    if ($this->hasHandlerForType($type)) {
      $handler = $this->getHandlerForType($type);
      $data = $handler->getTransformedJson($component);
      if (empty($data)) {
        return $data;
      }
      $children = $component->getChildren();
      foreach ($children as $child) {
        $childrenData = [];
        if ($child->getProperty('type') === 'container' && $child->getProperty('isContainer')) {
          $childrenData = [
            'type' => 'container',
            'id' => $child->getUUID(),
          ];
          foreach ($child->iterateChildren() as $element) {
            $componentData = $this->processComponent($element);
            if (!empty($componentData)) {
              $childrenData['data'][] = $this->processComponent($element);
            }
          }
        }
        if (!empty($childrenData)) {
          $data['data']['children'][] = $childrenData;
        }
      }
    }
    return $data;
  }

  public function getSchema() {
    if (!isset($this->schema)) {
      $this->buildSchema();
    }
    return $this->schema;
  }

  protected function buildSchema() {
    $fieldSchema = $this->formFieldManager->getStaticSchema();
    $this->schema['form_fields'] = $fieldSchema;

    foreach ($this->handlers as $type => $handler) {
      $this->schema['components'][$type] = $handler->getStaticSchema();
    }
  }

  /**
   * Finds Site Studio component "type".
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $component
   *   Site Studio Component object.
   * @return string
   *   Component "type".
   */
  protected function getComponentType(Element $component) {
    if ($component->isComponentContent()) {
      return 'component_content';
    }
    if ($component->isCustomComponent()) {
      return 'custom_component';
    }
    return 'component';
  }

}
