<?php

namespace Drupal\cohesion_sync\Exception;

use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * Thrown if package definition is invalid.
 */
class InvalidPackageDefinitionException extends InvalidArgumentException {

  const ERROR_MESSAGE = 'Invalid package definition. Expected to found array with "type" and "source" properties, found "%s" instead.';

  /**
   * InvalidPackageDefinitionException constructor.
   *
   * @param string $type
   *   Variable type.
   */
  public function __construct(string $type) {
    $msg = sprintf(self::ERROR_MESSAGE, $type);

    parent::__construct($msg);
  }

}
