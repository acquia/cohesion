<?php

namespace Drupal\cohesion_sync\Exception;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Thrown if package definition is missing required properties.
 */
class PackageDefinitionMissingPropertiesException extends InvalidArgumentException {

  const ERROR_MESSAGE = 'Package definition is missing the following %s: %s. Package definition must have "%s" properties and they cannot be empty.';

  /**
   * PackageDefinitionMissingPropertiesException constructor.
   *
   * @param array $missing_properties
   *   Missing Package definition properties.
   * @param array $required_properties
   *   Properties required by Package definition.
   */
  public function __construct(array $missing_properties = [], $required_properties = []) {
    $missing = 1 == count($missing_properties) ? 'property' : 'properties';
    $msg = sprintf(self::ERROR_MESSAGE,
      $missing,
      implode(", ", $missing_properties),
      implode(", ", $required_properties)
    );

    parent::__construct($msg);
  }

}
