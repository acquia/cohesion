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
class LayoutCanvas implements LayoutCanvasElementInterface, \JsonSerializable {

  /**
   * The raw canvas
   *
   * @var object
   */
  protected $raw_decoded_canvas;

  /**
   * The top elements in the canvas.
   *
   * @var canvasElements\Drupal\cohesion\LayoutCanvas\Element[]
   */
  protected $canvasElements = [];

  /**
   * The top elements in the component form.
   *
   * @var componentFormElements\Drupal\cohesion\LayoutCanvas\Element[]
   */
  protected $componentFormElements = NULL;

  /**
   * The top elements in the style guide form.
   *
   * @var componentFormElements\Drupal\cohesion\LayoutCanvas\Element[]
   */
  protected $styleGuideFormElements = NULL;

  /**
   * The angular mapper.
   *
   * @var mapper
   */
  protected $mapper;

  /**
   * The angular previewModel.
   *
   * @var previewModel
   */
  protected $previewModel = NULL;

  /**
   * The angular variableFields.
   *
   * @var variableFields
   */
  protected $variableFields = NULL;

  /**
   * The angular disabledNodes.
   *
   * @var disabledNodes
   */
  protected $disabledNodes = NULL;

  /**
   * Content obfuscated before being sent to the API.
   *
   * @var array
   */
  protected $hashed_content = [];

  /**
   * @var bool
   */
  protected $is_api_ready = FALSE;

  /**
   * Meta information.
   *
   * @var null
   */
  protected $meta = NULL;

  /**
   * LayoutCanvas constructor.
   *
   * @param $json_values
   */
  public function __construct($json_values) {
    $decoded_json_values = json_decode($json_values);
    $this->raw_decoded_canvas = json_decode($json_values);

    $model = property_exists($decoded_json_values, 'model') && is_object($decoded_json_values->model) ? $decoded_json_values->model : FALSE;

    if (property_exists($decoded_json_values, 'previewModel')) {
      $this->previewModel = $decoded_json_values->previewModel;
    }

    if (property_exists($decoded_json_values, 'variableFields')) {
      $this->variableFields = $decoded_json_values->variableFields;
    }

    if (property_exists($decoded_json_values, 'mapper')) {
      $this->mapper = $decoded_json_values->mapper;
    }

    if (property_exists($decoded_json_values, 'canvas') && is_array($decoded_json_values->canvas)) {
      foreach ($decoded_json_values->canvas as $index => $element) {
        $this->canvasElements[$index] = new Element($element, $this, $model);
      }
    }

    if (property_exists($decoded_json_values, 'componentForm')) {
      $this->componentFormElements = [];
      if (is_array($decoded_json_values->componentForm)) {
        foreach ($decoded_json_values->componentForm as $index => $element) {
          $this->componentFormElements[$index] = new Element($element, $this, $model);
        }
      }
    }

    if (property_exists($decoded_json_values, 'styleGuideForm')) {
      $this->styleGuideFormElements = [];
      if (is_array($decoded_json_values->styleGuideForm)) {
        foreach ($decoded_json_values->styleGuideForm as $index => $element) {
          $this->styleGuideFormElements[$index] = new Element($element, $this, $model);
        }
      }
    }

    if (property_exists($decoded_json_values, 'disabledNodes')) {
      $this->disabledNodes = $decoded_json_values->disabledNodes;
    }

    if (property_exists($decoded_json_values, 'meta')) {
      $this->meta = $decoded_json_values->meta;
    }
  }

  /**
   *
   */
  public function getCanvasElements() {
    return $this->canvasElements;
  }

  /**
   * @return array
   */
  public function getContentHashed() {
    return $this->hashed_content;
  }

  /**
   * @return bool
   */
  public function hasElements() {
    foreach ($this->iterateCanvas() as $item) {
      if ($item->isElement()) {
        // Found an element so return.
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Returns all elements in the layout canvas as a flat array.
   *
   * @return \Drupal\cohesion\LayoutCanvas\Element[]
   */
  public function iterateCanvas() {
    $elements = [];

    foreach ($this->canvasElements as $element) {
      $elements[] = $element;
      $elements = array_merge($elements, $element->iterateChildren());
    }

    return $elements;
  }

  /**
   * Return all component from elements as a flat array.
   *
   * @return \Drupal\cohesion\LayoutCanvas\Element[]
   */
  public function iterateComponentForm() {
    $elements = [];

    if (!is_null($this->componentFormElements)) {
      foreach ($this->componentFormElements as $element) {
        $elements[] = $element;
        $elements = array_merge($elements, $element->iterateChildren());
      }
    }

    return $elements;
  }

  /**
   * Return all style guide from elements as a flat array.
   *
   * @return \Drupal\cohesion\LayoutCanvas\Element[]
   */
  public function iterateStyleGuideForm() {
    $elements = [];

    if (!is_null($this->styleGuideFormElements)) {
      foreach ($this->styleGuideFormElements as $element) {
        $elements[] = $element;
        $elements = array_merge($elements, $element->iterateChildren());
      }
    }

    return $elements;
  }

  /**
   * Return whether the object is ready to be sent to the API.
   *
   * @return bool
   */
  public function isApiReady() {
    return $this->is_api_ready;
  }

  /**
   * Loop.
   *
   * @param string $type
   *   all|canvas|component_form|style_guide_form
   *   Specify which models to be return, Only the canvas or only the component
   *   form or both.
   *
   * @return \Drupal\cohesion\LayoutCanvas\ElementModel[]
   */
  public function iterateModels($type = 'all') {
    $models = [];

    if ($type == 'all' || $type == 'canvas') {
      // Loop over the canvas.
      foreach ($this->iterateCanvas() as $element) {
        if ($element->getModel()) {
          $models[$element->getModelUUID()] = $element->getModel();
        }
      }
    }

    if ($type == 'all' || $type == 'component_form') {
      // Loop over the component form.
      foreach ($this->iterateComponentForm() as $element) {
        if ($element->getModel()) {
          $models[$element->getModelUUID()] = $element->getModel();
        }
      }
    }

    if ($type == 'all' || $type == 'style_guide_form') {
      // Loop over the component form.
      foreach ($this->iterateStyleGuideForm() as $element) {
        if ($element->getModel()) {
          $models[$element->getModelUUID()] = $element->getModel();
        }
      }
    }

    return $models;
  }

  /**
   * Get references to entities on components and component content.
   *
   * @return array
   */
  public function getEntityReferences() {
    $references = [];

    foreach ($this->iterateCanvas() as $element) {
      if ($element->isComponentContent()) {
        $references[] = [
          'entity_type' => 'component_content',
          'entity_id' => $element->getComponentContentId(),
        ];
      }

      if ($element->getModel()) {
        foreach ($element->getModel()->getValues() as $key => $value) {
          if (preg_match(ElementModel::MATCH_UUID, $key) && is_object($value)) {
            if (property_exists($value, 'entity') && property_exists($value, 'entity_type')) {
              // Entity reference.
              $references[] = [
                'entity_type' => $value->entity_type,
                'entity_id' => $value->entity,
              ];
            }
            elseif (property_exists($value, 'entity') && property_exists($value->entity, 'entityId') && property_exists($value->entity, 'entityType')) {
              // Entity browser.
              $references[] = [
                'entity_type' => $value->entity->entityType,
                'entity_id' => $value->entity->entityId,
              ];
            }
          }
        }
      }

    }

    return $references;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareDataForApi($is_preview) {
    if (!$this->isApiReady()) {
      $this->hashed_content = [];
      foreach ($this->iterateCanvas() as &$element) {
        if ($element->getModel()) {
          $element->prepareDataForAPI($is_preview);
          $this->hashed_content = array_merge($this->hashed_content, $element->getModel()->getHashedContent());
        }
      }
    }

    $this->is_api_ready = TRUE;
  }

  public function getRawDecodedJsonValues() {
    return $this->raw_decoded_canvas;
  }

  /**
   * @return array|\Drupal\cohesion\LayoutCanvas\Element[]|mixed
   */
  public function jsonSerialize() {
    $canvas = ['canvas' => $this->canvasElements];
    if (!is_null($this->componentFormElements)) {
      $canvas['componentForm'] = $this->componentFormElements;
    }
    if (!is_null($this->styleGuideFormElements)) {
      $canvas['styleGuideForm'] = $this->styleGuideFormElements;
    }
    $canvas['mapper'] = $this->mapper;
    if (!$this->isApiReady()) {
      $canvas['model'] = $this->iterateModels();
      if (!is_null($this->previewModel)) {
        $canvas['previewModel'] = $this->previewModel;
      }
      if (!is_null($this->variableFields)) {
        $canvas['variableFields'] = $this->variableFields;
      }
      if (!is_null($this->disabledNodes)) {
        $canvas['disabledNodes'] = $this->disabledNodes;
      }
      if (!is_null($this->meta)) {
        $canvas['meta'] = $this->meta;
      }
    }
    return $canvas;
  }

}
