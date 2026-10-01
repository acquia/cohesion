<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\CustomComponentsService;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio components data and schema.
 */
class CustomComponentHandler extends ComponentLevelBase {

  /**
   * Site Studio Component "type".
   */
  const TYPE = 'custom_component';

  /**
   * Component regex pattern.
   */
  const PATTERN = '^custom_component$';

  /**
   * @var \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface
   */
  protected $formFieldManager;

  /**
   * @var \Drupal\cohesion_elements\CustomComponentsService
   */
  protected $customComponentsService;

  /**
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   * @param \Drupal\cohesion_elements\CustomComponentsService $customComponentsService
   */
  public function __construct(
    FormFieldManagerInterface $formFieldManager,
    CustomComponentsService $customComponentsService,
  ) {
    parent::__construct($formFieldManager);
    $this->customComponentsService = $customComponentsService;
  }

  /**
   * {@inheritdoc}
   */
  public static function type(): string {
    return self::TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function pattern(): string {
    return self::PATTERN;
  }

  /**
   * {@inheritdoc}
   */
  public function getTransformedJson(Element $component):array {
    $componentConfig = $this->customComponentsService->getComponent($component->getComponentID());
    if (is_null($componentConfig)) {
      return [];
    }
    $fields = [];

    if (isset($componentConfig['form']) && $componentConfig['form'] instanceof LayoutCanvas) {
      $fields = $this->processForm($componentConfig['form'], $component->getModel());
    }

    return $this->buildComponentArray($component, $fields);
  }

}
