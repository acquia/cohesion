<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
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

  /**
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   */
  public function __construct(
      FormFieldManagerInterface $formFieldManager
  ) {
    $this->formFieldManager = $formFieldManager;
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
    $component_config = Component::load($component->getComponentID());
    $layoutCanvasInstance = $component_config->getLayoutCanvasInstance();
    $fields = [];

    if ($layoutCanvasInstance instanceof LayoutCanvas) {
      foreach ($layoutCanvasInstance->iterateComponentForm() as $form_field) {
        if ($layoutCanvasInstance !== $form_field->getParent()) {
          continue;
        }
        if ($form_field->getProperty('type') === 'form-container') {
          $containerFields = $this->processFormField($form_field, $component);
          $fields = array_merge(
            $fields,
            $containerFields
          );
          foreach ($form_field->getChildren() as $containerField) {
            $this->processedFields[] = $containerField->getUUID();
          }
        }
        elseif ($field = $this->processFormField($form_field, $component)) {
          $fields[] = $field;
        }
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
  public function getStaticJsonSchema() {
    $field_schema = $this->formFieldManager->getStaticSchema();

    return (object) [
      'type' => 'object',
      'properties' => [
        'type' => [
          'type' => 'string',
          'pattern' => self::PATTERN,
        ],
        'id' => ['type' => 'string'],
        'data' => [
          'type' => 'object',
          'properties' => [
            'uid' => ['type' => 'string'],
            'title' => ['type' => 'string'],
            'data' => '$definitions/form_fields',
          ],
        ],
        'required' => ['id', 'type', 'data'],
      ],
    ];
  }

  protected function processFormField(Element $form_field, Element $component): array {
    $field = [];

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
