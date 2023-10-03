<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldRepeaterHandler;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio components content data and schema.
 */
class ComponentContentHandler implements ComponentLevelHandlerInterface {

  /**
   * Site Studio Component "type".
   */
  const TYPE = 'component_content';

  /**
   * Component regex pattern.
   */
  const PATTERN = '^component_content';

  /**
   * Processed form fields uuids.
   *
   * @var array
   */
  protected $processedFields = [];

  /**
   * @var \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface
   */
  protected $formFieldManager;

  protected $fieldRepeaterHandler;

  /**
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   * @param \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldRepeaterHandler $fieldRepeaterHandler
   */
  public function __construct(
    FormFieldManagerInterface $formFieldManager,
    FormFieldRepeaterHandler $fieldRepeaterHandler
  ) {
    $this->formFieldManager = $formFieldManager;
    $this->fieldRepeaterHandler = $fieldRepeaterHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function type(): string {
    return self::TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSchema() {
    // @todo Implement getSchema() method.
  }

  /**
   * {@inheritdoc}
   */
  public function hasChildren() {
    // @todo Implement hasChildren() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getChildren() {
    // @todo Implement getChildren() method.
  }

  /**
   * {@inheritdoc}
   */
  public function getTransformedJson(Element $component) {
    $component_contents = \Drupal::entityTypeManager()
      ->getStorage('component_content')
      ->loadByProperties(['uuid' => $component->getComponentContentId()]);
    /** @var \Drupal\cohesion_elements\Entity\ComponentContent */
    $component_content = reset($component_contents);
    $this->model = reset($component_content->layout_canvas->entity->getLayoutCanvasInstance()->iterateModels());
    $fields = [];

    $component_config = Component::load($component->getComponentID());
    foreach ($component_config->getLayoutCanvasInstance()->iterateComponentForm() as $form_field) {
      if (!in_array($form_field->getUUID(), $this->processedFields) && $field = $this->processFormField($form_field, $component)) {
        $fields[] = $field;
      }
    }

    $json = [
      'type' => self::TYPE,
      'id' => $component->getUUID(),
      'data' => [
        'uid' => $component->getComponentID(),
        'title' => $component->getProperty('title'),
        'field_data' => $fields,
      ],
    ];

    return $json;
  }

  /**
   * {@inheritdoc}
   */
  protected function getSchemaFieldMap() {
    return [
      'type' => 'uid',
      'id' => 'uuid',
      'data' => [
        'title' => 'title',
        'field_data' => [],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticJsonSchema() {
    return (object) [
      'description' => 'Site Studio component.',
      'type' => 'object',
      'properties' => [
        'id' => ['type' => 'string'],
        'type' => [
          'type' => 'string',
          'pattern' => self::PATTERN,
        ],
        'data' => [
          'type' => 'object',
          'properties' => [
            'uid' => ['type' => 'string'],
            'title' => ['type' => 'string'],
            'field_data' => ['type' => 'array'],
          ],
        ],
        'required' => ['id', 'type', 'data'],
      ],
    ];
  }

  protected function processFormField(Element $form_field, Element $component): array {
    $field = [];

    if ($form_field->getProperty('type') === 'form-field-container') {
      $field = $this->fieldRepeaterHandler->getData($form_field, $this->model);
      foreach ($form_field->getChildren() as $child) {
        $this->processedFields[] = $child->getUUID();
      }
    }

    if (is_string($form_field->getProperty('uid')) && $this->formFieldManager->hasHandlerForType($form_field->getProperty('uid'))) {
      $field = $this->formFieldManager->getHandlerForType($form_field->getProperty('uid'))
        ->getData($form_field, $this->model);
    }
    if (!empty($field)) {
      $this->processedFields[] = $form_field->getUUID();
    }

    return $field;
  }

}
