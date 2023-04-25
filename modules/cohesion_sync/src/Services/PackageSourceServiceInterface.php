<?php

namespace Drupal\cohesion_sync\Services;

/**
 * Site Studio package source service interface.
 */
interface PackageSourceServiceInterface {

  /**
   * Checks if type is supported by service.
   *
   * @param string $type
   *   Source type, for example "default_module_package".
   *
   * @return bool
   *   Returns TRUE if type is supported, FALSE if not.
   */
  public function supportedType(string $type): bool;

  /**
   * Returns supported type.
   *
   * @return string $type
   *   Source type, for example "default_module_package".
   */
  public function getSupportedType(): string;

  /**
   * Prepares package sync directory.
   *
   * The purpose of this method is to allow package source service to handle
   * various types of package storage, such as local file systems, ftp, aws s3,
   * etc. Metadata is supplied in array format and can be customised for each
   * package source service.
   *
   * @param array $sourceMetadata
   *   Source Metadata, such as paths, urls, credentials, etc.
   *
   * @return string
   *   Path to sync directory, once package handling/downloading is finished.
   */
  public function preparePackage(array $sourceMetadata): string;

}
