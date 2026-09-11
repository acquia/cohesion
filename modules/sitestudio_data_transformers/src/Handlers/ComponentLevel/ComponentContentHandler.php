<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ComponentLevel;

use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_elements\Entity\Component;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface;

/**
 * Handles Site Studio components content data and schema.
 */
class ComponentContentHandler extends ComponentLevelBase {

  /**
   * Site Studio Component "type".
   */
  const TYPE = 'component_content';

  /**
   * Component regex pattern.
   */
  const PATTERN = '^component_content';

  /**
   * Processed form fields uuids.
   *
   * @var array
   */
  protected $processedFields = [];

  /**
   * @var \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface
   */
  protected $formFieldManager;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   * @param \Drupal\sitestudio_data_transformers\Services\FormFieldManagerInterface $formFieldManager
   */
  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    FormFieldManagerInterface $formFieldManager,
  ) {
    parent::__construct($formFieldManager);
    $this->entityTypeManager = $entityTypeManager;
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
  public function getTransformedJson(Element $component): array {
    $componentContents = $this->entityTypeManager->getStorage('component_content')
      ->loadByProperties(['uuid' => $component->getComponentContentId()]);
    /** @var \Drupal\cohesion_elements\Entity\ComponentContent|NULL $componentContent */
    $componentContent = reset($componentContents);
    if (!$componentContent instanceof ComponentContent) {
      return [];
    }
    $model = reset($componentContent->layout_canvas->entity->getLayoutCanvasInstance()->iterateModels());
    $fields = [];
    $componentConfig = Component::load($component->getComponentID());
    if (!$componentConfig instanceof Component) {
      return [];
    }
    $layoutCanvasInstance = $componentConfig->getLayoutCanvasInstance();

    if ($layoutCanvasInstance instanceof LayoutCanvas && $model) {
      $fields = $this->processForm($layoutCanvasInstance, $model);
    }

    return $this->buildComponentArray($component, $fields);
  }

}
