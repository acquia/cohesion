<?php

namespace Drupal\cohesion_sync\Exception;

/**
 *
 */
class UnsupportedFileFormatException extends \Exception {

  const ERROR_MESSAGE = 'File with unsupported format ("%s") detected. For more information refer to the documentation page: https://docs.acquia.com/drupal-starter-kits/add-ons/site-studio/docs/configuration/export-packages/package-export-overview/drush-commands';

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
