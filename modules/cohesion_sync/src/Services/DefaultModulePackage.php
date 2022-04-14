<?php

namespace Drupal\cohesion_sync\Services;

/**
 * Default Module Package service.
 */
class DefaultModulePackage implements PackageSourceServiceInterface {

  const SUPPORTED_TYPE = 'default_module_package';
  const MODULE_NAME_ERROR_MESSAGE = 'Default module package install attempted, but "module_name" property is missing in source metadata.';
  const PATH_ERROR_MESSAGE = 'Default module package install attempted for "%s", but "module_name" property is missing in source metadata.';

  /**
   * {@inheritdoc}
   */
  public function supportedType(string $type): bool {
    return $type === self::SUPPORTED_TYPE;
  }

  /**
   * Handles default module packages.
   *
   * No downloading/file moving actions are required, so we're
   * checking for correct metadata values and returning sync dir path.
   *
   * @param array $sourceMetadata
   *   Source metadata.
   *
   * @return string
   *   Path to default package directory in module.
   *
   * @throws \Exception
   *   Thrown if source metadata values are missing.
   */
  public function preparePackage(array $sourceMetadata): string {
    if (isset($sourceMetadata['module_name'])) {
      $module_path = drupal_get_path('module', $sourceMetadata['module_name']);
    }
    else {
      throw new \Exception(self::MODULE_NAME_ERROR_MESSAGE);
    }

    if (isset($sourceMetadata['path'])) {
      $package_path = $sourceMetadata['path'];
    }
    else {
      throw new \Exception(sprintf(self::PATH_ERROR_MESSAGE, $sourceMetadata['module_name']));
    }

    return $module_path . $package_path;
  }

}
