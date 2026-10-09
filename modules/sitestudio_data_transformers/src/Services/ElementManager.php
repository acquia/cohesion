<?php

namespace Drupal\sitestudio_data_transformers\Services;

use Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementLevelHandlerInterface;

/**
 * Service collector for element level handlers.
 */
class ElementManager implements ElementManagerInterface {

  /**
   * Form field level handler service collection.
   * @var \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementLevelHandlerInterface[]
   */
  protected $handlers = [];

  /**
   * @var array
   */
  protected $schema;

  /**
   * Adds handler service to collection.
   *
   * @param \Drupal\sitestudio_data_transformers\Handlers\ElementLevel\ElementLevelHandlerInterface $handler
   *   Element level handler service.
   */
  public function addHandler(ElementLevelHandlerInterface $handler): self {
    $this->handlers[$handler->id()] = $handler;

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function hasHandlerForType(string $fieldType): bool {
    return array_key_exists($fieldType, $this->handlers);
  }

  /**
   * {@inheritdoc}
   */
  public function getHandlerForType(string $fieldType): ElementLevelHandlerInterface {
    return $this->handlers[$fieldType];
  }

  /**
   * {@inheritdoc}
   * @return array
   */
  public function buildSchema(): array {
    $schema = [];

    foreach ($this->handlers as $type => $handler) {
      $schema[$type] = $handler->getStaticSchema();
    }

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getStaticSchema(): array {
    if (!isset($this->schema)) {
      $this->schema = $this->buildSchema();
    }

    return $this->schema;
  }

}
