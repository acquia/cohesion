<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio form fields of "field repeater" type.
 */
class FieldRepeaterHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-field-repeater';
  const MAP = '/maps/field/form-field-container.map.yml';
  const SCHEMA = '/maps/field/form-field-container.schema.json';

  /**
   * Form Field Manager service.
   * @var \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface
   */
  protected $formFieldManager;

  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    FormFieldManagerInterface $formFieldManager,
  ) {
    parent::__construct($moduleHandler);
    $this->formFieldManager = $formFieldManager;
  }

  public function getData(Element $formField, ElementModel $elementModel): array {
    $data = [
      'type' => self::ID,
      'id' => $formField->getUUID(),
      'machine_name' => $formField->getModel()->getProperty(['settings', 'machineName']),
    ];

    $childrenValues = [];
    $repeaterFields = [];
    $children = $this->getFormFieldChildren($formField);
    foreach ($children as $repeaterChild) {
      if ($repeaterChild instanceof Element) {
        $repeaterFields[$repeaterChild->getUUID()] = $repeaterChild;
      }
    }

    $repeaterValues = $elementModel->getProperty($formField->getUUID());
    foreach ($repeaterValues as $setKey => $set) {
      $group = [];
      foreach ($set as $key => $value) {
        if (!array_key_exists($key, $repeaterFields) || is_null($repeaterFields[$key])) {
          continue;
        }
        if ($this->formFieldManager->hasHandlerForType($repeaterFields[$key]->getProperty('uid'))) {
          $setField = $this->formFieldManager
            ->getHandlerForType($repeaterFields[$key]->getProperty('uid'))
            ->getData($repeaterFields[$key], $elementModel);
          if ($repeaterFields[$key]->getProperty('uid') === 'form-field-repeater') {
            $repeaterChildren = $this->getFormFieldChildren($repeaterFields[$key]);
            foreach ($repeaterChildren as $repeaterChild) {
              $repeaterChildData = $this->formFieldManager->getHandlerForType(
                $repeaterChild->getProperty('uid'))->getData($repeaterChild, $elementModel
              );
              if (isset($value[$setKey]->{$repeaterChildData['id']})) {
                $repeaterChildData['data']['value'] = $value[$setKey]->{$repeaterChildData['id']};
              }
              $setField['data']['children'][] = $repeaterChildData;
            }
            $group[] = $setField;
          }
          elseif ($repeaterFields[$key]->getProperty('type') === 'form-container') {
            foreach ($setField as $containerItem) {
              $containerItem['data']['value'] = $value;
              $group[] = $containerItem;
            }
          }
          else {
            $setField['data']['value'] = $value;
            $group[] = $setField;
          }
        }
      }
      if (!empty($group)) {
        $childrenValues[] = $group;
      }
    }

    $data['data']['children'] = $childrenValues;

    return $data;
  }

  /**
   * {@inheritdoc}
   */
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

  /**
   * Return form field children, skips nested field repeaters.
   *
   * @param \Drupal\cohesion\LayoutCanvas\Element $formField
   *
   * @return array
   */
  protected function getFormFieldChildren(Element $formField): array {
    $data = [];
    $children = $formField->getChildren();
    foreach ($children as $child) {
      if ($child->getProperty('uid') === 'form-field-repeater') {
        continue;
      }
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
