<?php

namespace Drupal\cohesion_sync\Exception;

use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

/**
 * This exception is thrown if non-existent Package Source Service is requested.
 */
class SourceServiceNotFoundException extends InvalidArgumentException implements NotFoundExceptionInterface {

  /**
   * @param string $type
   *   Package Source Service type.
   * @param array $alternatives
   *   Alternative Package Source Services.
   */
  public function __construct(string $type, array $alternatives = []) {
    $msg = sprintf('You have requested a non-existent source service type: "%s".', $type);
    if ($alternatives) {
      if (1 == \count($alternatives)) {
        $msg .= ' Did you mean this: "';
      }
      else {
        $msg .= ' Did you mean one of these: "';
      }
      $msg .= implode('", "', $alternatives) . '"?';
    }

    parent::__construct($msg);
  }

}
