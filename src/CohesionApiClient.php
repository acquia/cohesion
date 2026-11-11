<?php

namespace Drupal\cohesion;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Site\Settings;
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
   * Request uuid.
   *
   * @var string
   */
  protected $uuid;

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
   *
   */
  public function buildElements($payload) {
    return $this->send('POST', '/build/elements', $payload);
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
   *
   * @return array
   *
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  protected function send($method, $uri, $data = [], $json_as_object = FALSE, $retry = TRUE) {

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

    $compress = \Drupal::configFactory()->get('cohesion.settings')->get('compress_outbound_request');
    if ($compress !== FALSE) {
      $options['headers']['Content-Encoding'] = 'gzip';
      // Compression level set to 1 to get the network performance without
      // affecting the client site performance
      $options['body'] = gzencode($body, 1);
    } else {
      $options['body'] = $body;
    }

    $code = NULL;
    $response_data = NULL;
    $with_message = TRUE;
    // Add drupal messages only if not dx8 api call.
    if (strpos(\Drupal::service('path.current')->getPath(), 'cohesionapi') !== FALSE) {
      $with_message = FALSE;
    }

    try {
      // Get the response from the API.
      $request = \Drupal::httpClient()->request($method, \Drupal::service('cohesion.api.utils')->getAPIServerURL() . $uri, $options);
      $code = $request->getStatusCode();
      if ($json_as_object) {
        $response_data = json_decode($request->getBody()->getContents());
      }
      else {
        $response_data = Json::decode($request->getBody()->getContents());
      }
    }
    catch (RequestException $e) {

      // If there are network errors, we need to ensure the application
      // doesn't crash. If $e->hasResponse is not null we can attempt to
      // get the message. Otherwise, we'll just pass a network
      // unavailable message.
      if ($e->hasResponse()) {
        $exception = (string) $e->getResponse()->getBody();
        $response_data = JSON::decode($exception);
        $code = $e->getCode();

        if (!$response_data['error']) {
          $response_data['error'] = substr(strip_tags($exception), 0, 1024);
        }

        \Drupal::logger('api-call-error')->error(
          t('API error: %message request_id: %uuid',
            ['%message' => $e->getMessage(), '%uuid' => $this->uuid]
          )
        );
      }
      else {
        // Retry the request.
        if ($retry) {
          // Wait in case the API is rebooting.
          sleep(5);
          // The final FALSE means we only re-try once.
          return $this->send($method, $uri, $data, $json_as_object, FALSE);
        }

        $code = 503;
        $response_data['error'] = $e->getMessage();
      }

      if ($with_message) {
        \Drupal::messenger()->addError(
          t('API error: %message request_id: %uuid',
            ['%message' => $e->getMessage(), '%uuid' => $this->uuid]
          )
        );
      }
    }

    return ['code' => $code, 'data' => $response_data];
  }

}
