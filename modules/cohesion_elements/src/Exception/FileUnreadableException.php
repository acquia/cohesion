<?php

namespace Drupal\cohesion_elements\Exception;

/**
 * An exception thrown by the ComponentDiscovery class.
 *
 * This would be triggered whilst parsing info.yml files.
 */
class FileUnreadableException extends \Exception {
  const MESSAGE = 'File exists, but is empty or unreadable at "%s".';

  public function __construct($path) {
    $message = sprintf(self::MESSAGE, $path);
    parent::__construct($message);
  }

}
