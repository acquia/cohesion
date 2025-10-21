<?php

namespace Drupal\cohesion;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\Utility\Error;

/**
 * Logs exceptions to Drupal logs.
 */
trait ExceptionLoggerTrait {

  /**
   * Exception message format.
   *
   * @var string
   */
  protected $exceptionMessageFormat = '%type: @message in %function (line %line of %file).';

  /**
   * The logger service.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected $logger;

  /**
   * Loggs exception in specified or default 'cohesion' channel.
   *
   * @param \Exception $exception
   * @param string $channel
   *
   * @return void
   */
  protected function logException(\Exception $exception, string $channel = 'cohesion') {
    $logger = $this->getLogger($channel);
    $variables = Error::decodeException($exception);

    $logger->error($this->exceptionMessageFormat, $variables);
  }

  /**
   * Gets channel via any of the available methods.
   *
   * @param string $channel
   *
   * @return \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected function getLogger(string $channel): LoggerChannelInterface {
    if (method_exists($this, 'getLogger')) {
      return $this->getLogger($channel);
    }

    if ($this->logger instanceof LoggerChannelFactoryInterface) {
      return $this->logger->get($channel);
    }

    if ($this->logger instanceof LoggerChannelInterface) {
      return $this->logger;
    }

    return \Drupal::logger($channel);
  }

}
