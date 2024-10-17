<?php

namespace Drupal\sitestudio_data_transformers\Services;

use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormContainerHandler;
use Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldLevelHandlerInterface;

/**
 * Service collector for form field level handlers.
 */
class FormFieldManager implements FormFieldManagerInterface {

  /**
   * Form field level handler service collection.
   * @var \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldLevelHandlerInterface[]
   */
  protected $handlers = [];

  /**
   * @var array
   */
  protected $schema;

  /**
   * Adds handler service to collection.
   *
   * @param \Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel\FormFieldLevelHandlerInterface $handler
   *   Form field level handler service.
   */
  public function addHandler(FormFieldLevelHandlerInterface $handler): self {
    if ($handler instanceof FormContainerHandler) {
      foreach ($handler::CONTAINER_UIDS as $uid) {
        $this->handlers[$uid] = $handler;
      }
      return $this;
    }
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
  public function getHandlerForType(string $fieldType): FormFieldLevelHandlerInterface {
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
