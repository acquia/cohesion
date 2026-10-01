<?php

namespace Drupal\cohesion_elements\Exception;

/**
 * This would be triggered whilst parsing files.
 */
class FileNotFoundException extends \Exception {
  const MESSAGE = 'No file found at "%s".';

  public function __construct($path) {
    $message = sprintf(self::MESSAGE, $path);
    parent::__construct($message);
  }

}
