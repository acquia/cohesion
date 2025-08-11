<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Component\Serialization\Yaml;

/**
 * Base class for element handlers.
 */
abstract class ElementHandlerBase implements ElementLevelHandlerInterface {

  const MAP = '';
  const SCHEMA = '';
  const ID = '';
  const MODULE_NAME = 'sitestudio_data_transformers';

  /**
   * @var array
   */
  protected $map;

  /**
   * @var \Drupal\cohesion\LayoutCanvas\Element
   */
  protected $element;

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
   * {@inheritdoc}
   */
  public function id(): string {
    return $this::ID;
  }

  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
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
   * @param \Drupal\cohesion\LayoutCanvas\Element $element
   * @return array
   */
  public function getData(Element $element, ElementModel $elementModel): array {
    $this->element = $element;
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
    $data = [];

    if (is_array($item) && isset($item['_property'])) {
      return $this->processProperty($item);
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
      case 'element':
        $property = $this->element->getProperty($item['path']);
        break;

      case 'model':
        $property = $this->element->getModel()->getProperty($item['path']);
        break;

      case 'constant':
        $property = $item['value'];
        break;

      default:
        return NULL;
    }
    return $property;
  }

}
