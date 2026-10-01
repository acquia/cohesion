<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\cohesion\LayoutCanvas\Element;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 *
 */
class ImageHandler extends FieldHandlerBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-image';
  const MAP = '/maps/field/image.map.yml';
  const SCHEMA = '/maps/field/image.schema.json';

  /**
   * @var \Drupal\cohesion\ImageBrowserUpdateManager
   */
  protected $browserUpdateManager;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    ImageBrowserUpdateManager $browserUpdateManager,
    FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    parent::__construct($moduleHandler);
    $this->browserUpdateManager = $browserUpdateManager;
    $this->fileUrlGenerator = $fileUrlGenerator;
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
    $property = parent::processProperty($item);

    if (isset($item['_process_token']) && is_string($property)) {
      $property = $this->processToken($property);
    }

    return $property;
  }

  /**
   * Processes image token and returns absolute file URL.
   *
   * @param string $property
   * @return string
   */
  protected function processToken(string $property) {
    $processedToken = $this->browserUpdateManager->decodeToken($property);

    return $this->fileUrlGenerator->generateAbsoluteString($processedToken['path']);
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return self::ID;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(Element $formField, ElementModel $elementModel): array {
    $this->formField = $formField;
    $this->elementModel = $elementModel;
    return parent::getData($formField, $elementModel);
  }

}
