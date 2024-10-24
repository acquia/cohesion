<?php

namespace Drupal\cohesion_sync\Services;

use Drupal\cohesion_sync\Exception\PackageSourceMissingPropertiesException;
use Drupal\Core\Extension\MissingDependencyException;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\Exception\DirectoryNotReadyException;
use Drupal\Core\Extension\ExtensionPathResolver;

/**
 * Default Module Package service.
 */
class DefaultModulePackage implements PackageSourceServiceInterface {

  const SUPPORTED_TYPE = 'default_module_package';
  const REQUIRED_PROPERTIES = ['module_name', 'path'];

  /**
   * ModuleHandler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Factory for getting extension lists by type.
   *
   * @var \Drupal\Core\Extension\ExtensionPathResolver
   */
  protected $extensionPathResolver;

  /**
   * DefaultModulePackage constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   Module handler service.
   * @param \Drupal\Core\Extension\ExtensionPathResolver $extensionPathResolver
   *   Factory for getting extension lists by type.
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    ExtensionPathResolver $extensionPathResolver,
  ) {
    $this->moduleHandler = $moduleHandler;
    $this->extensionPathResolver = $extensionPathResolver;
  }

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
    $this->validateMetadata($sourceMetadata);

    $module_path = $this->extensionPathResolver->getPath('module', $sourceMetadata['module_name']);
    $package_path = $sourceMetadata['path'];

    return $module_path . '/' . $package_path;
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
      throw new PackageSourceMissingPropertiesException(self::SUPPORTED_TYPE, $missing_properties, self::REQUIRED_PROPERTIES);
    }
    if ($this->moduleHandler->moduleExists($sourceMetadata['module_name']) !== TRUE) {
      throw new MissingDependencyException(sprintf('Unable to install default module package due to missing module %s.', $sourceMetadata['module_name']));
    }

    $module_path = $this->extensionPathResolver->getPath('module', $sourceMetadata['module_name']);
    $package_path = $module_path . '/' . $sourceMetadata['path'];
    if (!is_dir($package_path) || !is_readable($package_path)) {
      throw new DirectoryNotReadyException(sprintf('Directory "%s" is not found.', $package_path));
    }
  }

}
