<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

use Drupal\cohesion\ImageBrowserUpdateManager;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;

/**
 * Handles Site Studio elements that have Images & transforms the tokens to
 * readable paths.
 */
abstract class ImageHandlerBase extends ElementHandlerBase implements ElementLevelHandlerInterface {
  const ID = '';
  const MAP = '';
  const SCHEMA = '';

  /**
   * @var \Drupal\cohesion\ImageBrowserUpdateManager
   */
  protected $browserUpdateManager;

  /**
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\cohesion\ImageBrowserUpdateManager $browserUpdateManager
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   */
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

}
