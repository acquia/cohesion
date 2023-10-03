<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio form fields of "input" type.
 */
class FormFieldRepeaterHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  /**
   * Site Studio Element type id.
   * @todo make these injectable from DIC or at least configurable/changeable by clients.
   */
  const ID = 'form-field-container';
  const MAP = '/maps/field_level/form-field-container.map.yml';
  const SCHEMA = '/maps/field_level/form-field-container.schema.json';

  /**
   * Form Field Manager service.
   * @var \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface
   */
  protected $formFieldManager;

  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    FormFieldManagerInterface $formFieldManager
  ) {
    parent::__construct($moduleHandler);
    $this->formFieldManager = $formFieldManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicSchema(Element $form_field = NULL): array {

    if (is_null($form_field)) {
      return json_decode($this->schema, TRUE);
    }

    $settings = $form_field->getModel()->getProperty('settings');
    if (isset($settings->schema) && !empty($settings->schema)) {
      $schema = json_decode($this->schema, TRUE);
      if (isset($settings->schema->maxLength)) {
        //@todo make this cleaner
        $schema['properties']['attributes']['properties']['value']['maxLength'] = $settings->schema->maxLength;
      }
    }

    return $schema;
  }

  public function getData(Element $form_field, ElementModel $elementModel): array {
    $data = [
      'type' => 'form-field-container',
      'id' => $form_field->getUUID(),
    ];

    $children = [];
    $repeater_values = $elementModel->getProperty($form_field->getUUID());
    $repeater_fields = [];
    foreach ($form_field->getChildren() as $repeater_field) {
      $repeater_fields[$repeater_field->getUUID()] = $repeater_field;
    }
    foreach ($repeater_values as $set) {
      $group = [];
      foreach ($set as $key => $value) {
        if (!array_key_exists($key, $repeater_fields) || is_null($repeater_fields[$key])) {
          continue;
        }
        if ($this->formFieldManager->hasHandlerForType($repeater_fields[$key]->getProperty('uid'))) {
          $set_field = $this->formFieldManager->getHandlerForType($repeater_fields[$key]->getProperty('uid'))->getData($repeater_fields[$key], $elementModel);
          $set_field['data']['value'] = $value;
          $group[] = $set_field;
        }
      }
      if (!empty($group)) {
        $children[] = $group;
      }
    }

    if (!empty($children)) {
      $data['data']['children'] = $children;
    }

    return $data;
  }

  protected function processItem($item): mixed {
    return parent::processItem($item);
  }

  protected function processProperty(array $item): mixed {
    $property = parent::processProperty($item);

    if (is_null($property) && $item['component-model']) {
      $paths = $this->elementModel->getLeavesWithPathToRoot();
      foreach ($paths as $path) {
        foreach ($item['path'] as $key) {
          if ($path['key'] === $key) {
            $property = $this->elementModel->getProperty($path['path']);
            break;
          }
        }
      }
    }

    return $property;
  }

}
