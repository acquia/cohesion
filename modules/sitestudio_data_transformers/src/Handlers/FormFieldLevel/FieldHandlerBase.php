<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Serialization\Yaml;

/**
 * Base class for field handlers.
 */
abstract class FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const MAP = '';
  const SCHEMA = '';
  const ID = '';
  const MODULE_NAME = 'sitestudio_data_transformers';

  /**
   * Form field element.
   *
   * @var \Drupal\cohesion\LayoutCanvas\Element
   */
  protected $formField;

  /**
   * @var array
   */
  protected $map;

  /**
   * @var \Drupal\cohesion\LayoutCanvas\ElementModel
   */
  protected $elementModel;

  /**
   * JSON Schema of the field.
   *
   * @var array|null
   */
  protected $schema;

  /**
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   */
  public function __construct(ModuleHandlerInterface $moduleHandler) {
    $modulePath = $moduleHandler->getModule(self::MODULE_NAME)->getPath();

    if (is_file($modulePath . $this::SCHEMA)) {
      $schemaFile = file_get_contents($modulePath . $this::SCHEMA);
      $this->schema = json_decode($schemaFile, TRUE);
    }
    else {
      $this->schema = [];
    }

    if (is_file($modulePath . $this::MAP)) {
      $mapFile = file_get_contents($modulePath . $this::MAP);
      $this->map = Yaml::decode($mapFile);
    }
    else {
      $this->map = [];
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getStaticSchema(): array {
    return $this->schema;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this::ID;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(Element $formField, ElementModel $elementModel): array {
    $this->formField = $formField;
    $this->elementModel = $elementModel;
    $data = [];
    foreach ($this->map as $key => $item) {
      $data[$key] = $this->processItem($item);
    }

    return $data;
  }

  /**
   * Processes item in a map.
   *
   * @param $item
   * @return array
   *   Array of data.
   */
  protected function processItem($item): mixed {
    if (is_array($item) && isset($item['_property'])) {
      return $this->processProperty($item);
    }
    if (is_array($item) && isset($item['_dynamic_property'])) {
      return $this->processDynamicProperty($item);
    }
    if (is_array($item)) {
      foreach ($item as $key => $sub_item) {
        $data[$key] = $this->processItem($sub_item);
      }
    }
    return $data;
  }

  /**
   * Converts `_property` array to data array.
   *
   * @param array $item
   *   Array containing property type and path.
   *
   * @return mixed
   *   Value stored in the property.
   */
  protected function processProperty(array $item): mixed {
    switch($item['type']) {
      case 'form-element':
        $property = $this->formField->getProperty($item['path']);
        break;

      case 'form-model':
        $property = $this->formField->getModel()->getProperty($item['path']);
        break;

      case 'component-model':
        $property = $this->elementModel->getProperty($item['path']);
        if (is_null($property) && !is_null($this->formField->getModel()->getProperty($item['path']))) {
          $property = $this->formField->getModel()->getProperty($item['path']);
        }
        break;

      case 'constant':
        $property = $item['value'];
        break;

      default:
        return NULL;
    }
    return $property;
  }

  /**
   * @param array $item
   *
   * @return mixed
   */
  protected function processDynamicProperty(array $item): mixed {
    $path = [];

    // Path is built from dynamic/static values available at runtime.
    foreach ($item['path'] as $dynamicPathItem) {
      $path[] = $this->processItem($dynamicPathItem);
    }

    // Now that the path is built, convert to regular property.
    unset($item['_dynamic_property']);
    $item['_property'] = TRUE;
    $item['path'] = $path;

    return $this->processItem($item);
  }

}
