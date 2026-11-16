<?php

namespace Drupal\cohesion\LayoutCanvas;

/**
 * Class LayoutCanvas.
 *
 * @package Drupal\cohesion
 *
 * @Api(
 *   id = "cohesion_layout_canvas",
 *   name = @Translation("Layout canvas object"),
 * )
 */
class Element implements LayoutCanvasElementInterface, \JsonSerializable {

  /**
   * @var \Drupal\cohesion\LayoutCanvas\Element|\Drupal\cohesion\LayoutCanvas\LayoutCanvas
   */
  protected $parent;

  /**
   * @var \Drupal\cohesion\LayoutCanvas\Element[]
   */
  protected $children = [];

  /**
   * @var object
   */
  protected $original;

  /**
   * @var \Drupal\cohesion\LayoutCanvas\ElementModel|null
   */
  protected $model;

  /**
   * The element properties without children.
   *
   * @var object
   */
  protected $element;

  /**
   * @var bool
   */
  protected $is_api_ready = FALSE;

  /**
   * Element constructor.
   *
   * @param object $raw_element
   *   The element object as it is after json_decode.
   * @param \Drupal\cohesion\LayoutCanvas\Element|\Drupal\cohesion\LayoutCanvas\LayoutCanvas $parent
   *   The parent of the element. Either other Element or the LayoutCanvas.
   * @param object|false $raw_model
   *   The whole model as it is after json_decode.
   */
  public function __construct($raw_element, $parent, $raw_model) {
    $this->parent = $parent;
    $this->original = clone $raw_element;

    $children = [];
    if (property_exists($raw_element, 'children')) {
      if (is_array($raw_element->children)) {
        $children = $raw_element->children;
      }

      unset($raw_element->children);
    }

    $this->element = clone $raw_element;

    if ($raw_model && is_object($raw_model) && property_exists($raw_model, $this->getModelUUID())) {
      $this->model = new ElementModel($raw_model->{$this->getModelUUID()}, $this);
    }

    // Set the parentUid.
    if ($this->parent instanceof Element) {
      $this->setProperty('parentUid', $this->parent->getProperty('uid'));
    }
    elseif ($this->parent instanceof LayoutCanvas) {
      $this->setProperty('parentUid', 'root');
    }

    foreach ($children as $index => $child) {
      $this->children[$index] = new Element($child, $this, $raw_model);
    }
  }

  /**
   * Find a property in the element.
   *
   * @param string|array $path_to_property
   *   The path in the element to get this property. Specify a string if top
   *   level, or an array to search in leaves.
   *
   * @return mixed|null
   */
  public function getProperty($path_to_property) {
    $property_names = [];

    if (is_string($path_to_property)) {
      $property_names = [$path_to_property];
    }
    elseif (is_array($path_to_property)) {
      $property_names = $path_to_property;
    }

    $current_pointer = $this->element;
    foreach ($property_names as $property_name) {
      if (is_object($current_pointer) && property_exists($current_pointer, $property_name)) {
        $current_pointer = $current_pointer->{$property_name};
      }
      else {
        return NULL;
      }
    }

    return $current_pointer;
  }

  /**
   * @return \Drupal\cohesion\LayoutCanvas\Element|\Drupal\cohesion\LayoutCanvas\LayoutCanvas
   */
  public function getParent() {
    return $this->parent;
  }

  /**
   * @param $property_name
   */
  public function unsetProperty($property_name) {
    if (property_exists($this->element, $property_name)) {
      unset($this->element->{$property_name});
    }
  }

  /**
   * @param $property_name
   * @param $value
   */
  public function setProperty($property_name, $value) {
    $this->element->{$property_name} = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    return $this->children;
  }

  /**
   * @return \Drupal\cohesion\LayoutCanvas\ElementModel|null
   */
  public function getModel() {
    return $this->model;
  }

  /**
   * Return the element uuid.
   *
   * @return null|string
   */
  public function getUUID() {
    return (property_exists($this->element, 'uuid')) ? $this->element->uuid : NULL;
  }

  /**
   * @return mixed|null|string
   */
  public function getModelUUID() {
    return $this->getUUID();
  }

  /**
   * Return TRUE if the element is a component content.
   *
   * @return bool
   */
  public function isComponentContent() {
    return !is_null($this->getProperty('componentContentId'));
  }

  /**
   * Return TRUE if the element is a custom component.
   *
   * @return bool
   */
  public function isCustomComponent() {
    return !is_null($this->getProperty('isCustomComponent')) && $this->getProperty('isCustomComponent') === TRUE;
  }

  /**
   * Get the component content id.
   *
   * @return mixed|object|string|string[]|null
   */
  public function getComponentContentId() {
    if ($this->isComponentContent()) {
      return str_replace('cc_', '', $this->getProperty('componentContentId'));
    }

    return NULL;
  }

  /**
   * Return TRUE if the element is a component.
   *
   * @return bool
   */
  public function isComponent() {
    return property_exists($this->element, 'type') && $this->element->type == 'component' && property_exists($this->element, 'componentId');
  }

  /**
   * Return TRUE if element is an element.
   *
   * @return bool
   */
  public function isElement() {
    return !property_exists($this->element, 'componentId') && !property_exists($this->element, 'dropzoneId');
  }

  /**
   * @return int|null
   */
  public function getComponentID() {
    if ($this->isComponent()) {
      return $this->element->componentId;
    }

    return NULL;
  }

  /**
   * @return bool
   */
  public function isApiReady() {
    return $this->is_api_ready;
  }

  /**
   * Recursively loop through the element children.
   *
   * Returns a flat array of Element[] of the element children.
   *
   * Ex:
   *  -> first_child
   *      -> first_child_child_1
   *      -> first_child_child_2
   *        -> first_child_child_2_child_1
   *  -> second_child
   *      -> second_child_child_1
   *
   * would return
   *  -> first_child
   *  -> first_child_child_1
   *  -> first_child_child_2
   *  -> first_child_child_2_child_1
   *  -> second_child
   *  -> second_child_child_1
   *
   * @return \Drupal\cohesion\LayoutCanvas\Element[]
   */
  public function iterateChildren() {
    $elements = [];

    if (count($this->getChildren()) > 0) {
      foreach ($this->getChildren() as $child) {
        $elements[] = $child;
        $elements = array_merge($elements, $child->iterateChildren());
      }
    }

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareDataForAPI($is_preview) {
    if ($this->getModel()) {
      $this->getModel()->prepareDataForAPI();
    }
    $this->is_api_ready = TRUE;
  }

  /**
   * {@inheritdoc}
   */
  #[\ReturnTypeWillChange]
  public function jsonSerialize() {
    $json_obj = new \stdClass();
    foreach ($this->element as $property_name => $property_value) {
      $json_obj->{$property_name} = $property_value;
    }

    if ($this->isApiReady() && $this->getModel()) {
      $json_obj->model = $this->getModel();
    }

    $json_obj->children = $this->getChildren();

    return $json_obj;
  }

}
