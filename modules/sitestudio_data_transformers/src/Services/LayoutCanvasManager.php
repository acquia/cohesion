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
   * @var \Drupal\sitestudio_data_transformers\Services\ElementManagerInterface
   */
  protected $elementManager;

  /**
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   */
  public function __construct(
    FormFieldManagerInterface $formFieldManager,
    ElementManagerInterface $elementManager,
  ) {
    $this->formFieldManager = $formFieldManager;
    $this->elementManager = $elementManager;
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
      if ($element->isComponent()) {
        $componentData = $this->processComponent($element);
        if (!empty($componentData)) {
          $layoutCanvasJson[] = $componentData;
        }
      }
      elseif ($element->isElement()) {
        $elementData = $this->processElement($element);
        if (!is_null($elementData)) {
          $layoutCanvasJson[] = $elementData;
        }
      }
    }

    return $layoutCanvasJson;
  }

  /**
   * Processes Site Studio components.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $component
   *   Site Studio element object containing component.
   *
   * @return array
   *   Returns processed component or empty array.
   */
  protected function processComponent(Element $component): array {
    $data = [];
    $type = $this->getComponentType($component);
    if ($this->hasHandlerForType($type) && $component->getModel()) {
      $handler = $this->getHandlerForType($type);
      $data = $handler->getTransformedJson($component);
      if (empty($data)) {
        return $data;
      }
      $children = $component->getChildren();
      if (!empty($children)) {
        $childrenData = $this->getChildrenData($children);
        if (!empty($childrenData)) {
          $data['data']['children'] = $childrenData;
        }
      }
    }
    return $data;
  }

  /**
   * Processes Site Studio elements.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $element
   *   Site Studio element object.
   *
   * @return array
   *   Returns processed element or empty array.
   */
  protected function processElement(Element $element) {
    $type = $element->getProperty('uid');
    $elementModel = $element->getModel();
    if ($this->elementManager->hasHandlerForType($type)) {
      $handler = $this->elementManager->getHandlerForType($type);
    } elseif ($element->getProperty('isCustom') === TRUE) {
      $handler = $this->elementManager->getHandlerForType('custom-element');
    } else {
      return NULL;
    }

    $data = $handler->getData($element, $elementModel);
    if ($element->getChildren()) {
      $data['children'] = $this->getChildrenData($element->getChildren());
    }

    return $data;
  }

  /**
   * @return array
   */
  public function getSchema() {
    if (!isset($this->schema)) {
      $this->buildSchema();
    }
    return $this->schema;
  }

  /**
   * @return void
   */
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
   * @return string|bool
   *   Component "type" or false if $element is not a component.
   */
  protected function getComponentType(Element $component): string|false {
    if ($component->isComponentContent()) {
      return 'component_content';
    }
    if ($component->isCustomComponent()) {
      return 'custom_component';
    }
    if ($component->isComponent()) {
      return 'component';
    }

    return FALSE;
  }

  /**
   * @param array $children
   * @return array
   */
  protected function getChildrenData(array $children): array {
    $childrenData = [];
    foreach ($children as $child) {
      /**@var \Drupal\cohesion\LayoutCanvas\Element $child */
      if ($child->isComponent()) {
        $childrenData[] = $this->processComponent($child);
      }
      elseif ($child->isElement() && $this->elementManager->hasHandlerForType($child->getProperty('uid'))) {
        $elementData = $this->processElement($child);
        if (!is_null($elementData)) {
          $childrenData[] = $elementData;
        }
      }
      elseif ($child->getProperty('uid') === 'component-drop-zone') {
        $childrenData = [
          'type' => 'container',
          'id' => $child->getUUID(),
          'uid' => $child->getProperty('uid'),
        ];
        $childrenData['data']['children'] = $this->getChildrenData($child->getChildren());
      }
    }
    return $childrenData;
  }

}
