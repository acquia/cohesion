<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Serialization\Yaml;

/**
 *
 */
abstract class FieldHandlerBase implements FormFieldLevelHandlerInterface {
  /**
   * Site Studio Element type id.
   */
  const ID = 'undefined';
  const MAP = 'undefined';
  const SCHEMA = 'undefined';
  const MODULE_NAME = 'sitestudio_data_transformers';

  /**
   * Form field element.
   *
   * @var \Drupal\cohesion\LayoutCanvas\Element
   */
  protected $form_field;

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
    $module_path = $moduleHandler->getModule(self::MODULE_NAME)->getPath();
    $map_file = file_get_contents($module_path . $this::MAP);
    $schema_file = file_get_contents($module_path . $this::SCHEMA);

    $this->map = Yaml::decode($map_file);
    $this->schema = json_decode($schema_file, TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getDynamicSchema(Element $form_field = NULL): array {
    return $this->schema;
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
  public function getData(Element $form_field, ElementModel $elementModel): array {
    $this->form_field = $form_field;
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
        $property = $this->form_field->getProperty($item['path']);
        break;

      case 'form-model':
        $property = $this->form_field->getModel()->getProperty($item['path']);
        break;

      case 'component-model':
        $property = $this->elementModel->getProperty($item['path']);
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
    foreach ($item['path'] as $dynamic_path_item) {
      $path[] = $this->processItem($dynamic_path_item);
    }

    // Now that the path is built, convert to regular property.
    unset($item['_dynamic_property']);
    $item['_property'] = TRUE;
    $item['path'] = $path;

    return $this->processItem($item);
  }

}
