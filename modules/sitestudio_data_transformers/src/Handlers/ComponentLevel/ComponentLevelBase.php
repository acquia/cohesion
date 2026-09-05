<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Base class for component level handlers.
 */
abstract class ComponentLevelBase implements ComponentLevelHandlerInterface {

  const PATTERN = '';
  const TYPE = '';

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
    FormFieldManagerInterface $formFieldManager,
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
  public function pattern(): string {
    return self::PATTERN;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticSchema(): array {

    return [
      'type' => 'object',
      'properties' => [
        'type' => [
          'type' => 'string',
          'pattern' => $this->pattern(),
        ],
        'id' => ['type' => 'string'],
        'machine_name' => ['type' => 'string'],
        'data' => [
          'type' => 'object',
          'properties' => [
            'title' => ['type' => 'string'],
            'data' => '#/definitions/form_fields',
          ],
        ],
        'required' => ['id', 'type', 'data'],
      ],
    ];
  }

  protected function processForm(LayoutCanvas $form, ElementModel $model): array {
    $formFields = [];

    foreach ($form->iterateComponentForm() as $formField) {
      if ($form !== $formField->getParent()) {
        continue;
      }
      if ($formField->getProperty('type') === 'form-container') {
        $containerFields = $this->processFormField($formField, $model);
        $formFields = array_merge(
          $formFields,
          $containerFields
        );
        foreach ($formField->getChildren() as $containerField) {
          $this->processedFields[] = $containerField->getUUID();
        }
      }
      elseif ($field = $this->processFormField($formField, $model)) {
        $formFields[] = $field;
      }
    }

    return $formFields;
  }

  /**
   * Process component form field.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $formField
   * @param \Drupal\cohesion\LayoutCanvas\ElementModel $model
   *
   * @return array
   */
  protected function processFormField(Element $formField, ElementModel $model): array {
    $field = [];

    if (is_string($formField->getProperty('uid')) && $this->formFieldManager->hasHandlerForType($formField->getProperty('uid'))) {
      $field = $this->formFieldManager->getHandlerForType($formField->getProperty('uid'))
        ->getData($formField, $model);
    }
    if (!empty($field)) {
      $this->processedFields[] = $formField->getUUID();
    }

    return $field;
  }

  protected function buildComponentArray(Element $component, array $fields): array {
    return [
      'type' => $this->type(),
      'id' => $component->getUUID(),
      'machine_name' => $component->getComponentID(),
      'data' => [
        'title' => $component->getModel()->getProperty(['settings', 'title']) ?? $component->getProperty('title'),
        'field_data' => $fields,
      ],
    ];
  }

}
