<?php

namespace Drupal\cohesion_sync\Exception;

/**
 * Thrown if legacy package format supplied to the new format import.
 */
class LegacyPackageFormatException extends \Exception {

  const ERROR_MESSAGE = 'File with legacy package format ("%s") detected. Legacy package format is not supported by this command. For more information refer to the documentation page: https://sitestudiodocs.acquia.com/6.9/user-guide/deprecating-legacy-package-system';

  public function __construct(string $filename, $code = 0, ?\Throwable $previous = NULL) {
    $message = sprintf(self::ERROR_MESSAGE, $filename);

    parent::__construct($message, $code, $previous);
  }

}
