<?php

namespace Drupal\cohesion_sync\Exception;

use Symfony\Component\Filesystem\Exception\FileNotFoundException;

/**
 * Thrown if package list is empty or missing.
 */
class PackageListEmptyOrMissing extends FileNotFoundException {
  const MESSAGE = 'Provided package list ("%s") is empty or doesn\'t exist.';

  /**
   * PackageListEmptyOrMissing constructor.
   *
   * @param string|null $path
   *   Package List path.
   */
  public function __construct(?string $path = NULL) {
    $message = sprintf(self::MESSAGE, $path);

    parent::__construct($message);
  }

}
