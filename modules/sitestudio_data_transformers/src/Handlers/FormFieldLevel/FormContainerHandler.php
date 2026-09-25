<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio form fields of "form container" type.
 */
class FormContainerHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const CONTAINER_UIDS = [
    'form-accordion',
    'form-tab-container',
    'form-tab-item',
    'form-section',
  ];

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

  /**
   * {@inheritdoc}
   */
  public function getStaticSchema(): array {
    return [];
  }

  public function getData(Element $formField, ElementModel $elementModel): array {
    $children = $formField->getChildren();
    $data = [];
    foreach ($children as $child) {
      if ($this->formFieldManager->hasHandlerForType($child->getProperty('uid'))) {
        if ($child->getProperty('type') === 'form-container') {
          $data = array_merge($data, $this->formFieldManager
            ->getHandlerForType($child->getProperty('uid'))
            ->getData($child, $elementModel));
        }
        else {
          $data[] = $this->formFieldManager
            ->getHandlerForType($child->getProperty('uid'))
            ->getData($child, $elementModel);
        }
      }
    }

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

}
