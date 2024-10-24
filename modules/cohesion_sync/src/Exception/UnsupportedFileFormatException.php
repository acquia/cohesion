<?php

namespace Drupal\cohesion_sync\Exception;

/**
 *
 */
class UnsupportedFileFormatException extends \Exception {

  const ERROR_MESSAGE = 'File with unsupported format ("%s") detected. For more information refer to the documentation page: https://sitestudiodocs.acquia.com/6.9/user-guide/package-export-using-drush';

  /**
   * @param string $filename
   * @param $code
   * @param \Throwable|NULL $previous
   */
  public function __construct(string $filename, $code = 0, ?\Throwable $previous = NULL) {
    $message = sprintf(self::ERROR_MESSAGE, $filename);

    parent::__construct($message, $code, $previous);
  }

}
