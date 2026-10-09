<?php

namespace Drupal\Tests\cohesion\Unit\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityJsonValuesTrait;

/**
 * Class MockUpdateEntity.
 *
 * @package Drupal\Tests\cohesion\Unit
 */
class EntityMockBase implements EntityJsonValuesInterface {

  use EntityJsonValuesTrait;

  protected $jsonValues;
  protected $isLayoutCanvas;

  public function __construct($json_values, $isLayoutCanvas = FALSE) {
    $this->jsonValues = $json_values;
    $this->isLayoutCanvas = $isLayoutCanvas;
  }

  public function getJsonValues() {
    return $this->jsonValues;
  }

  public function setJsonValue($json_values) {
    $this->jsonValues = $json_values;
    return $this;
  }

  public function getJsonMapper() {
    return '';
  }

  public function setJsonMapper($mapper) {
    return '';
  }

  public function process() {
    return '';
  }

  public function jsonValuesErrors() {
    return '';
  }

  public function isLayoutCanvas() {
    return $this->isLayoutCanvas;
  }

  public function getApiPluginInstance() {
    return '';
  }

}
