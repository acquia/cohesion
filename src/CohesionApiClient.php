<?php

namespace Drupal\cohesion;

use Drupal\cohesion\Services\ApiResponseHandler;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Site\Settings;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

/**
 * Client to perform API calls to Site Studio API.
 *
 * Class CohesionApiClient.
 *
 * @package Drupal\cohesion
 */
class CohesionApiClient {

  /**
   * Seconds to wait before retrying a failed request.
   *
   * @var int
   */
  const RETRY_DELAY = 5;

  /**
   * Request uuid.
   *
   * @var string
   */
  protected $uuid;

  /**
   * Pauses execution before a retry.
   *
   * Isolated from send() so tests can override it without a real delay.
   *
   * @param int $seconds
   */
  protected function wait($seconds) {
    sleep($seconds);
  }

  /**
   *
   */
  public function buildStyle($payload) {
    return $this->send('POST', '/build/style', $payload);
  }

  /**
   *
   */
  public function buildDeleteStyle($payload) {
    return $this->send('DELETE', '/build/style', $payload);
  }

  /**
   *
   */
  public function buildTemplate($payload) {
    return $this->send('POST', '/build/template', $payload);
  }

  /**
   * Build media queries used for default elements CSS libraries.
   */
  public function buildMediaQueries($payload) {
    return $this->send('POST', '/build/media-queries', $payload);
  }

  /**
   *
   */
  public function getAssetConfig() {
    return $this->send('GET', '/assets/config');
  }

  /**
   *
   */
  public function resourceIcon($payload) {
    return $this->send('POST', '/resource/icon', $payload);
  }

  /**
   *
   */
  public function valiatePMC($payload) {
    return $this->send('POST', '/validate/pmc', $payload);
  }

  /**
   * Merge component data with layout canvas
   */
  public function layoutCanvasDataMerge($payload) {
    return $this->send('POST', '/components/update', $payload, TRUE);
  }

  /**
   *
   */
  public function parseJson($command, $payload) {
    return $this->send('POST', '/parse/' . $command, $payload, TRUE);
  }

  /**
   * @return array
   */
  public function requestHeaders() {
    $cohesion_configs = \Drupal::config('cohesion.settings');
    $this->uuid = \Drupal::service('uuid')->generate();

    return [
      'dx8-env' => !empty($_ENV['AH_PRODUCTION']) && $_ENV['AH_PRODUCTION'] === 1 ? 'production' : Settings::get('dx8_env', 'non-production'),
      'dx8-site-id' => \Drupal::config('system.site')->get('uuid'),
      'dx8-api-key' => $cohesion_configs->get('api_key'),
      'dx8-drupal-path' => \Drupal::request()->getRequestUri(),
      'dx8-organization-key' => $cohesion_configs->get('organization_key'),
      'dx8-base-root' => $GLOBALS['base_root'],
      'dx8-version' => \Drupal::service('cohesion.api.utils')->getApiVersionNumber(),
      'X-Request-ID' => $this->uuid,
    ];
  }

  /**
   * @param $form_uri
   *
   * @return \Psr\Http\Message\ResponseInterface
   */
  public function getAssetJson($form_uri) {
    // Add authentication headers.
    $options = [
      'headers' => $this->requestHeaders(),
    ];

    return \Drupal::httpClient()->get(\Drupal::service('cohesion.api.utils')->getAPIServerURL() . $form_uri, $options);
  }

  /**
   * Entry point to sending to the API.
   * Applied outbound and (optional) inbound compression.
   *
   * @param $method
   * @param $uri
   * @param array $data
   * @param bool $json_as_object
   * @param bool $retry
   * @param bool $fresh_connect
   *   Force a brand new connection instead of reusing a pooled keep-alive one.
   *   Only set on a retry, where the previous socket may be stale.
   *
   * @return array
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function send($method, $uri, $data = [], $json_as_object = FALSE, $retry = TRUE, $fresh_connect = FALSE) {
    $body = Json::encode($data);

    // Build the headers for all requests.
    $options = [
      'headers' => array_merge([
        'Content-Type' => 'application/json; charset=utf-8',
        'Accept-Encoding' => 'gzip',
      ], $this->requestHeaders()),
      // Decompress inbound content.
      'decode_content' => TRUE,
    ];

    // Pooling is kept for normal requests to avoid a TCP/TLS handshake on every
    // call. On a retry we force a fresh connection.
    if ($fresh_connect) {
      $options['curl'] = [
        CURLOPT_FORBID_REUSE => TRUE,
        CURLOPT_FRESH_CONNECT => TRUE,
      ];
    }

    $compress = \Drupal::configFactory()->get('cohesion.settings')->get('compress_outbound_request');
    if ($compress !== FALSE) {
      $options['headers']['Content-Encoding'] = 'gzip';
      // Compression level set to 1 to get the network performance without
      // affecting the client site performance
      $options['body'] = gzencode($body, 1);
    } else {
      $options['body'] = $body;
    }

    $response_data = NULL;
    $with_message = TRUE;
    $request_start = microtime(TRUE);
    // Add drupal messages only if not dx8 api call.
    if (strpos(\Drupal::service('path.current')->getPath(), 'cohesionapi') !== FALSE) {
      $with_message = FALSE;
    }

    try {
      // Get the response from the API.
      $request = \Drupal::httpClient()->request($method, \Drupal::service('cohesion.api.utils')->getAPIServerURL() . $uri, $options);
      $code = $request->getStatusCode();
      $request_duration = microtime(TRUE) - $request_start;

      if ($json_as_object) {
        $response_data = json_decode($request->getBody()->getContents());
      }
      else {
        $response_data = Json::decode($request->getBody()->getContents());
      }
    }
    catch (RequestException $e) {
      $request_duration = microtime(TRUE) - $request_start;
      $code = $e->getCode();

      // Retry selected connection/send failures when no HTTP response exists.
      $transient_curl_errors = [55, 35];
      $curl_errno = $e->getHandlerContext()['errno'] ?? NULL;
      if ($retry && !$e->hasResponse() && in_array($curl_errno, $transient_curl_errors, TRUE)) {
        // Wait in case the API is temporarily unavailable.
        $this->wait(self::RETRY_DELAY);
        // FALSE: only re-try once. TRUE: force a fresh connection in case the
        // failure was a stale-pooled socket.
        return $this->send($method, $uri, $data, $json_as_object, FALSE, TRUE);
      }

      if ($e->hasResponse()) {
        $exception = (string) $e->getResponse()->getBody();

        // The response body may not be valid JSON
        // decode natively and fallback to an empty array.
        $response_data = json_decode($exception, TRUE);
        if (!is_array($response_data)) {
          $response_data = [];
        }

        if (empty($response_data['error'])) {
          $response_data['error'] = substr(strip_tags($exception), 0, 4096);
        }

        // Log the full response body rather than $e->getMessage(), which
        // Guzzle truncates to a short summary ending in "(truncated...)".
        \Drupal::logger('cohesion_api')->error(
          t('API error %code: %message request_id: %uuid',
            ['%code' => $code, '%message' => $exception, '%uuid' => $this->uuid]
          )
        );
      }
      else {
        // No response means the request may have reached the server before
        // failing (e.g. a read timeout).
        // Set the code as the request was not completed.
        $code = 503;
        $response_data['error'] = $e->getMessage();
      }

      if ($with_message) {
        \Drupal::messenger()->addError(
          t('API request error %code: %message request_id: %uuid',
            ['%code' => $code, '%message' => $e->getMessage(), '%uuid' => $this->uuid]
          )
        );
      }
    }
    catch (ConnectException $e) {
      // A connection could not be established. Retry once in case the API is
      // temporarily unavailable (e.g. rebooting).
      if ($retry) {
        // Wait in case the API is rebooting.
        $this->wait(self::RETRY_DELAY);
        // FALSE: only re-try once. TRUE: force a fresh connection in case the
        // failure was a stale-pooled socket.
        return $this->send($method, $uri, $data, $json_as_object, FALSE, TRUE);
      }

      $request_duration = microtime(TRUE) - $request_start;
      // Set the code as the connection was not completed.
      $code = 503;

      \Drupal::logger('cohesion_api')->error(
        t('API connection error %code: %message request_id: %uuid',
          ['%code' => $code, '%message' => $e->getMessage(), '%uuid' => $this->uuid]
        )
      );

      if ($with_message) {
        \Drupal::messenger()->addError(
          t('API connection error %code: %message request_id: %uuid',
            ['%code' => $code, '%message' => $e->getMessage(), '%uuid' => $this->uuid]
          )
        );
      }
    }

    return [
      'method' => $method,
      'uri' => $uri,
      'code' => $code,
      'request_id' => $this->uuid,
      'exception_message' => isset($e) ? $e->getMessage() : '',
      'data' => $response_data,
      'request_duration' => $request_duration,
      'payload' => $data,
      'is_auth_error' => in_array($code, ApiResponseHandler::AUTH_ERROR_CODES, TRUE),
    ];
  }

}
