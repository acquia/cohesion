<?php

namespace Drupal\cohesion\LayoutCanvas;

/**
 * Parser for Layout canvas to hold data in a structured way.
 *
 * @package Drupal\cohesion
 *
 * @Api(
 *   id = "cohesion_layout_canvas",
 *   name = @Translation("Layout canvas object"),
 * )
 */
class LayoutCanvas implements LayoutCanvasElementInterface, \JsonSerializable {

  const MEDIA_REFERENCE_REGEX = '/\[media-reference:(.*?)\]/m';
  const LINK_REFERENCE_REGEX = '/([a-z_-]+)::(\d+)/m';
  const ENTITY_REFERENCES = [
    [
      'id' => 'entity',
      'type' => 'entity_type',
    ],
    [
      'id' => 'entityId',
      'type' => 'entityType',
    ],
  ];

  /**
   * The raw canvas
   *
   * @var object
   */
  protected $raw_decoded_canvas = NULL;

  /**
   * The json canvas as stored in database
   *
   * @var string
   */
  protected $json_values;

  /**
   * The top elements in the canvas.
   *
   * @var \Drupal\cohesion\LayoutCanvas\Element[]
   */
  protected $canvasElements = [];

  /**
   * The top elements in the component form.
   *
   * @var \Drupal\cohesion\LayoutCanvas\Element[]
   */
  protected $componentFormElements = NULL;

  /**
   * The top elements in the style guide form.
   *
   * @var \Drupal\cohesion\LayoutCanvas\Element[]
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
    $this->json_values = $json_values;
    $decoded_json_values = json_decode($json_values);

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
   *  Check if a helper is a form helper.
   * @return bool
   */
  public function isFormHelper() {
    foreach ($this->iterateCanvas() as $item) {
      if ($i = $item->getModel()) {
        $element_type = $i->getElement()->getProperty('type');

        if ($element_type === 'form-field') {
          return TRUE;
        }
      }
    }
    return FALSE;
  }

  /**
   * Find a property in the layout canvas.
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

    $current_pointer = $this;
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
   * Remove a property from the layout canvas
   *
   * @param $property_name
   */
  public function unsetProperty($path_to_property) {
    $property_names = [];

    if (is_string($path_to_property)) {
      $property_names = [$path_to_property];
    }
    elseif (is_array($path_to_property)) {
      $property_names = $path_to_property;
    }

    $current_pointer = $this;
    foreach ($property_names as $index => $property_name) {
      if (is_object($current_pointer) && property_exists($current_pointer, $property_name)) {
        if($index + 1 === count($property_names)) {
          unset($current_pointer->{$property_name});
        } else {
          $current_pointer = $current_pointer->{$property_name};
        }
      }
      elseif (is_array($current_pointer) && isset($current_pointer[$property_name])) {
        if($index + 1 === count($property_names)) {
          unset($current_pointer->{$property_name});
        } else {
          $current_pointer = $current_pointer[$property_name];
        }
      }
    }
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
   * @param bool $withMediaReferences
   *   True to include media-reference entities.
   * @param bool $withLinksReferences
   *   True to include references to entities in links.
   *
   * @return array
   *   Array of entity reference ids/uuids and types.
   */
  public function getEntityReferences(bool $withMediaReferences = FALSE, bool $withLinksReferences = FALSE): array {
    $references = [];

    foreach ($this->iterateCanvas() as $element) {
      if ($element->isComponentContent()) {
        $references[] = [
          'entity_type' => 'component_content',
          'entity_id' => $element->getComponentContentId(),
        ];
      }

      if ($element->getModel()) {
        $leaves = $element->getModel()->getLeavesWithPathToRoot();
        foreach ($leaves as $leaf) {
          foreach (self::ENTITY_REFERENCES as $entity_reference) {
            // Let's check if key is of entity reference or browser.
            if ($leaf['key'] === $entity_reference['id']) {
              // Double check path and set pointer to end.
              if (end($leaf['path']) == $entity_reference['id']) {
                $path = $leaf['path'];
                // Leverage pointer being set to end and get entity type.
                $path[key($leaf['path'])] = $entity_reference['type'];
                // Attempt fetching type property from the model.
                $type = $element->getModel()->getProperty($path);
                if ($type) {
                  $reference = [
                    'entity_type' => $type,
                    'entity_id' => $leaf['value'],
                  ];
                  if (!in_array($reference, $references)) {
                    $references[] = $reference;
                  }
                }
              }
            }
          }
          if ($withMediaReferences && is_string($leaf['value']) && preg_match(self::MEDIA_REFERENCE_REGEX, $leaf['value'])) {
            if ($media_reference = $this->decodeMediaReferenceToken($leaf['value'])) {
              $reference = [
                'entity_type' => $media_reference[1],
                'entity_id' => $media_reference[2],
              ];
              if (!in_array($reference, $references)) {
                $references[] = $reference;
              }
            }
          }
          if ($withLinksReferences && is_string($leaf['value']) && preg_match(self::LINK_REFERENCE_REGEX, $leaf['value'])) {
            if ($link_reference = $this->decodeLinkReference($leaf['value'])) {
              $reference = [
                'entity_type' => $link_reference[0],
                'entity_id' => $link_reference[1],
              ];
              if (!in_array($reference, $references)) {
                $references[] = $reference;
              }
            }
          }
        }

      }

    }

    return $references;
  }

  /**
   * Fetches array of entities referenced via links in Layout Canvas.
   *
   * @return array
   *   Array of link references.
   */
  public function getLinksReferences(): array {
    $links = [];
    foreach ($this->iterateCanvas() as $element) {
      if ($element->getModel()) {
        $leaves = $element->getModel()->getLeavesWithPathToRoot();
        foreach ($leaves as $leaf) {
          if (is_string($leaf['value']) && preg_match(self::LINK_REFERENCE_REGEX, $leaf['value'])) {
            $link = $this->decodeLinkReference($leaf['value']);
            if (is_array($link) && !empty($link)) {
              $links[$element->getUUID()][] = [
                'entity_type' => $link[0],
                'entity_id' => $link[1],
                'path' => $leaf['path'],
              ];
            }
          }
        }
      }
    }

    return $links;
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
    if($this->raw_decoded_canvas == NULL) {
      $this->raw_decoded_canvas = json_decode($this->json_values);
    }

    return $this->raw_decoded_canvas;
  }

  public function getJsonValuesDecodedArray() {
    return json_decode($this->json_values, TRUE);
  }

  /**
   * @return array|\Drupal\cohesion\LayoutCanvas\Element[]|mixed
   */
  #[\ReturnTypeWillChange]
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

  /**
   * Decodes a [media-reference:?:?] token and return the Uri.
   *
   * @param string $token
   *   Token to decode.
   *
   * @return array|bool
   *   Array with entity reference or FALSE.
   */
  protected function decodeMediaReferenceToken(string $token) {
    return explode(':', str_replace(['[', ']'], '', $token));
  }

  /**
   * Decodes a "type::id" token and return the Uri.
   *
   * @param string $token
   *   Token to decode.
   *
   * @return array|bool
   *   Array with entity reference or FALSE.
   */
  protected function decodeLinkReference(string $token) {
    return explode('::', $token);
  }

}
