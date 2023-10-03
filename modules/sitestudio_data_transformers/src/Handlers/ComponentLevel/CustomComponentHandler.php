<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldRepeaterHandler;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio components data and schema.
 */
class CustomComponentHandler implements ComponentLevelHandlerInterface {

  /**
   * Site Studio Component "type".
   */
  const TYPE = 'custom_component';

  /**
   * Component regex pattern.
   */
  const PATTERN = '^custom_component$';

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

  /**
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponentsService;

  protected $fieldRepeaterHandler;

  /**
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponentsService
   * @param \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldRepeaterHandler $fieldRepeaterHandler
   */
  public function __construct(
    FormFieldManagerInterface $formFieldManager,
    CustomComponentsService $customComponentsService,
    FormFieldRepeaterHandler $fieldRepeaterHandler
  ) {
    $this->formFieldManager = $formFieldManager;
    $this->customComponentsService = $customComponentsService;
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
    $component_config = $this->customComponentsService->getComponent($component->getComponentID());
    if (is_null($component_config)) {
      return NULL;
    }

    $json = [
      'type' => self::TYPE,
      'id' => $component->getUUID(),
      'data' => [
        'uid' => $component->getComponentID(),
        'title' => $component->getProperty('title'),
      ],
    ];

    if (isset($component_config['form']) && $component_config['form'] instanceof LayoutCanvas) {
      $fields = [];
      foreach ($component_config['form']->iterateComponentForm() as $form_field) {
        if (!in_array($form_field->getUUID(), $this->processedFields) && $field = $this->processFormField($form_field, $component)) {
          $fields[] = $field;
        }
      }
      if (!empty($fields)) {
        $json['data']['field_data'] = $fields;
      }
    }

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
      $field = $this->fieldRepeaterHandler->getData($form_field, $component->getModel());
      foreach ($form_field->getChildren() as $child) {
        $this->processedFields[] = $child->getUUID();
      }
    }

    if (is_string($form_field->getProperty('uid')) && $this->formFieldManager->hasHandlerForType($form_field->getProperty('uid'))) {
      $field = $this->formFieldManager->getHandlerForType($form_field->getProperty('uid'))->getData($form_field, $component->getModel());
    }
    if (!empty($field)) {
      $this->processedFields[] = $form_field->getUUID();
    }

    return $field;
  }

}
