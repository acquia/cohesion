<?php

namespace Drupal\cohesion_elements\Exception;

/**
 * An exception thrown by the ComponentDiscovery class.
 *
 * This would be triggered whilst parsing info.yml files.
 */
class CustomComponentMissingPropertiesException extends \RuntimeException {

  const MESSAGE = 'Can\'t load custom component "%s" because of missing %s. Expected to find "%s", but "%s" %s %s missing.';

  /**
   * @param string $type
   *   Package source type.
   * @param array $missing_properties
   *   Missing Package definition properties.
   * @param array $required_properties
   *   Properties required by Package definition.
   */
  public function __construct(string $name, array $required_properties = [], $missing_properties = []) {
    $property = 1 == count($missing_properties) ? 'property' : 'properties';
    $is = 1 == count($missing_properties) ? 'property is' : 'properties are';

    $msg = sprintf(self::MESSAGE,
      $name,
      $property,
      implode(", ", $required_properties),
      implode(", ", $missing_properties),
      $property,
      $is
    );

    parent::__construct($msg);
  }

}
