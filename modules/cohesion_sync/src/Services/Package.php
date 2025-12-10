<?php

namespace Drupal\cohesion_sync\Services;

use Drupal\cohesion_sync\Exception\PackageSourceMissingPropertiesException;
use Drupal\Core\File\Exception\DirectoryNotReadyException;

/**
 * Package source service.
 */
class Package implements PackageSourceServiceInterface {

  const SUPPORTED_TYPE = 'package';
  const REQUIRED_PROPERTIES = ['path'];

  /**
   * {@inheritdoc}
   */
  public function supportedType(string $type): bool {
    return $type === self::SUPPORTED_TYPE;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedType(): string {
    return self::SUPPORTED_TYPE;
  }

  /**
   * Handles packages.
   *
   * No downloading/file moving actions are required, so we're
   * checking for correct metadata values and returning sync dir path.
   *
   * @param array $sourceMetadata
   *   Source metadata.
   *
   * @throws \Drupal\cohesion_sync\Exception\PackageDefinitionMissingPropertiesException
   *   Thrown if required properties are missing in definition.
   */
  public function preparePackage(array $sourceMetadata): string {
    $this->validateMetadata($sourceMetadata);

    return $sourceMetadata['path'];
  }

  /**
   * Validates Source metadata.
   *
   * @param array $sourceMetadata
   *   Metadata passed to Source service.
   *
   * @return void
   */
  protected function validateMetadata(array $sourceMetadata) {
    $missing_properties = [];
    foreach (self::REQUIRED_PROPERTIES as $property) {
      if (!isset($sourceMetadata[$property])) {
        $missing_properties[] = $property;
      }
    }

    if (!empty($missing_properties)) {
      throw new PackageSourceMissingPropertiesException(
        self::SUPPORTED_TYPE,
        $missing_properties,
        self::REQUIRED_PROPERTIES
      );
    }

    if (strpos($sourceMetadata['path'], '/') !== 0) {
      $sourceMetadata['path'] = DRUPAL_ROOT . '/' . $sourceMetadata['path'];
    }

    if (!is_dir($sourceMetadata['path']) || !is_readable($sourceMetadata['path'])) {
      throw new DirectoryNotReadyException(sprintf('Directory "%s" is not found or is unreadable.', $sourceMetadata['path']));
    }
  }

}
