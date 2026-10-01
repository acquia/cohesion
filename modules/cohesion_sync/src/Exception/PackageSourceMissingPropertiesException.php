<?php

namespace Drupal\cohesion_sync\Exception;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Thrown if package source section is missing required properties.
 */
class PackageSourceMissingPropertiesException extends InvalidArgumentException {

  const ERROR_MESSAGE = 'Package source section is missing the following %s: %s. Package type "%s" source section must have %s: %s.';

  /**
   * PackageSourceMissingPropertiesException constructor.
   *
   * @param string $type
   *   Package source type.
   * @param array $missing_properties
   *   Missing Package definition properties.
   * @param array $required_properties
   *   Properties required by Package definition.
   */
  public function __construct(string $type, array $missing_properties = [], $required_properties = []) {
    $missing = 1 == count($missing_properties) ? 'property' : 'properties';
    $required = 1 == count($required_properties) ? 'property' : 'properties';
    $msg = sprintf(self::ERROR_MESSAGE,
      $missing,
      implode(", ", $missing_properties),
      $type,
      $required,
      implode(", ", $required_properties)
    );

    parent::__construct($msg);
  }

}
