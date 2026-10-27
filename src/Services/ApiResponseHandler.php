<?php

namespace Drupal\cohesion\Services;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Handles API response processing and error detection.
 *
 * @package Drupal\cohesion\Services
 */
class ApiResponseHandler {
  use StringTranslationTrait;

  /**
   * HTTP status codes that indicate authentication/authorization failures.
   */
  public const AUTH_ERROR_CODES = [401, 403];

  /**
   * The logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * ApiResponseHandler constructor.
   *
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $stringTranslation
   *   The string translation service.
   */
  public function __construct(
    LoggerChannelFactoryInterface $loggerFactory,
    MessengerInterface $messenger,
    TranslationInterface $stringTranslation,
  ) {
    $this->logger = $loggerFactory->get('cohesion');
    $this->messenger = $messenger;
    $this->stringTranslation = $stringTranslation;
  }

  /**
   * Check if an API response indicates an authentication error.
   *
   * @param array $response
   *   The API response array from CohesionApiClient.
   *
   * @return bool
   *   TRUE if the response indicates an authentication error (401/403).
   */
  public function isAuthError(array $response): bool {
    return !empty($response['is_auth_error']);
  }

  /**
   * Check for authentication errors and handle them in a batch context.
   *
   * This method should be called after each API call during batch operations.
   * If an authentication error is detected, it sets the appropriate error
   * flags in the batch context to halt further processing and provides a
   * clear, actionable error message to the operator.
   *
   * @param array $response
   *   The API response array from CohesionApiClient.
   * @param array $context
   *   The batch context array (passed by reference).
   *
   * @return bool
   *   TRUE if an authentication error was detected and handled,
   *   FALSE otherwise.
   */
  public function handleBatchAuthError(array $response, array &$context): bool {
    if (!$this->isAuthError($response)) {
      return FALSE;
    }

    $errorMessage = $this->t(
      'Site Studio API authentication failed (HTTP @code). Please verify your API key and Organization key in Site Studio Account Settings (/admin/cohesion/configuration/account-settings). The rebuild has been stopped. Request ID: @request_id',
      [
        '@code' => $response['code'],
        '@request_id' => $response['request_id'] ?? 'unknown',
      ]
    );

    // Set batch context flags to halt further processing.
    $context['results']['error'] = $errorMessage;
    $context['results']['auth_error'] = TRUE;

    // Log the error for administrators.
    $this->logger->error($errorMessage);

    // Display the error to the user.
    $this->messenger->addError($errorMessage);

    return TRUE;
  }

  /**
   * Get the list of HTTP status codes considered authentication errors.
   *
   * @return array
   *   Array of HTTP status codes.
   */
  public function getAuthErrorCodes(): array {
    return self::AUTH_ERROR_CODES;
  }

}
