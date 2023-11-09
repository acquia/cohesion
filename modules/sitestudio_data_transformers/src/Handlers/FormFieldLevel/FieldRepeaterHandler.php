<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio form fields of "input" type.
 */
class FieldRepeaterHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  /**
   * Site Studio Element type id.
   * @todo make these injectable from DIC or at least configurable/changeable by clients.
   */
  const ID = 'form-field-repeater';
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
      'type' => self::ID,
      'id' => $form_field->getUUID(),
      'machine_name' => $form_field->getModel()->getProperty(['settings', 'machineName']),
    ];

    $children_values = [];
    $repeater_values = $elementModel->getProperty($form_field->getUUID());
    $repeater_fields = [];
    $children = $this->getFormFieldChildren($form_field);
    foreach ($children as $repeater_field) {
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
          if ($repeater_fields[$key]->getProperty('type') === 'form-container') {
            foreach ($set_field as $container_item) {
              $container_item['data']['value'] = $value;
              $group[] = $container_item;
            }
          }
          else {
            $set_field['data']['value'] = $value;
            $group[] = $set_field;
          }
        }
      }
      if (!empty($group)) {
        $children_values[] = $group;
      }
    }
    $data['data']['children'] = $children_values;

    return $data;
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

  protected function getFormFieldChildren(Element $formField): array {
    $data = [];
    $children = $formField->getChildren();
    foreach ($children as $child) {
      if (!empty($child->getChildren())) {
        $data = array_merge($data, $this->getFormFieldChildren($child));
      }
      else {
        $data[] = $child;
      }
    }

    return $data;
  }

}
