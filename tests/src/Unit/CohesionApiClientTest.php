<?php

namespace Drupal\Tests\cohesion\Unit;

use Drupal\cohesion\CohesionApiClient;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Tests\UnitTestCase;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;

/**
 * @coversDefaultClass \Drupal\cohesion\CohesionApiClient
 * @group Cohesion
 */
class CohesionApiClientTest extends UnitTestCase {

  /**
   * The CohesionApiClient instance to test.
   *
   * @var \Drupal\cohesion\CohesionApiClient|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cohesionApiClient;

  /**
   * The HTTP client.
   *
   * @var \GuzzleHttp\ClientInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $httpClient;

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $configFactory;

  /**
   * The cohesion settings config.
   *
   * @var \Drupal\Core\Config\ImmutableConfig|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $cohesionConfig;

  /**
   * The API utils service.
   *
   * @var \Drupal\cohesion\ApiUtils|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $apiUtils;

  /**
   * The current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentPath;

  /**
   * The event dispatcher service.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $eventDispatcher;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Mock the HTTP client.
    $this->httpClient = $this->createMock(Client::class);

    // Mock config factory and cohesion settings.
    $this->cohesionConfig = $this->createMock(ImmutableConfig::class);
    $this->cohesionConfig->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['compress_outbound_request', FALSE],
        ['api_key', 'test_api_key'],
        ['organization_key', 'test_org_key'],
      ]);

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('cohesion.settings')
      ->willReturn($this->cohesionConfig);

    // Mock current path service.
    $this->currentPath = $this->createMock(CurrentPathStack::class);
    $this->currentPath->expects($this->any())
      ->method('getPath')
      ->willReturn('/some/path');

    // Mock API utils service.
    $this->apiUtils = $this->getMockBuilder('\Drupal\cohesion\ApiUtils')
      ->disableOriginalConstructor()
      ->getMock();
    $this->apiUtils->expects($this->any())
      ->method('getAPIServerURL')
      ->willReturn('https://api.cohesion.net');
    $this->apiUtils->expects($this->any())
      ->method('getApiVersionNumber')
      ->willReturn('2.0');

    // Mock event dispatcher service.
    $this->eventDispatcher = $this->createMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');

    // Set up the container with our mocked services.
    $container = new ContainerBuilder();
    $container->set('http_client', $this->httpClient);
    $container->set('config.factory', $this->configFactory);
    $container->set('cohesion.api.utils', $this->apiUtils);
    $container->set('path.current', $this->currentPath);
    $container->set('uuid', $this->getUuidServiceMock());
    $container->set('messenger', $this->getMessengerMock());
    $container->set('logger.factory', $this->getLoggerFactoryMock());
    $container->set('event_dispatcher', $this->eventDispatcher);
    \Drupal::setContainer($container);

    // Create a partial mock of CohesionApiClient to test the send method.
    $this->cohesionApiClient = $this->getMockBuilder(CohesionApiClient::class)
      ->onlyMethods(['requestHeaders'])
      ->getMock();
    $this->cohesionApiClient->expects($this->any())
      ->method('requestHeaders')
      ->willReturn([
        'dx8-api-key' => 'test_api_key',
        'dx8-organization-key' => 'test_org_key',
        'X-Request-ID' => 'test-uuid',
      ]);
  }

  /**
   * Mock UUID service that returns a predictable UUID.
   */
  protected function getUuidServiceMock() {
    $uuid = $this->getMockBuilder('\Drupal\Component\Uuid\UuidInterface')
      ->getMock();
    $uuid->expects($this->any())
      ->method('generate')
      ->willReturn('test-uuid');
    return $uuid;
  }

  /**
   * Mock messenger service.
   */
  protected function getMessengerMock() {
    $messenger = $this->getMockBuilder('\Drupal\Core\Messenger\MessengerInterface')
      ->getMock();
    return $messenger;
  }

  /**
   * Mock logger factory service.
   */
  protected function getLoggerFactoryMock() {
    $logger = $this->getMockBuilder('\Psr\Log\LoggerInterface')
      ->getMock();

    $loggerFactory = $this->getMockBuilder('\Drupal\Core\Logger\LoggerChannelFactoryInterface')
      ->getMock();
    $loggerFactory->expects($this->any())
      ->method('get')
      ->willReturn($logger);

    return $loggerFactory;
  }

  /**
   * Data provider for successful API requests.
   *
   * @return iterable
   *   Test cases with named keys.
   */
  public static function successfulApiRequestProvider(): iterable {
    yield 'basic JSON response' => [
      new Response(200, [], json_encode(['status' => 'success'])),
      ['status' => 'success'],
    ];
  }

  /**
   * @covers ::send
   *
   * @dataProvider successfulApiRequestProvider
   */
  public function testSendSuccess(Response $response, array $expectedData): void {
    $this->httpClient->expects($this->once())
      ->method('request')
      ->willReturn($response);

    $result = $this->invokeProtectedMethod($this->cohesionApiClient, 'send', ['GET', '/test']);

    $this->assertEquals($expectedData, $result['data']);
  }

  /**
   * Data provider for exception handling tests.
   *
   * @return iterable
   *   Test cases with named keys for exception scenarios.
   */
  public static function exceptionHandlingProvider(): iterable {
    $badRequestResponse = new Response(400, [], json_encode(['error' => 'Bad request']));
    $badRequest = new Request('GET', '/test');
    $requestException = new RequestException('Bad request', $badRequest, $badRequestResponse);

    yield 'RequestException with response body' => [
      $requestException,
      ['error' => 'Bad request'],
    ];

    $connectRequest = new Request('GET', '/test');
    $connectException = new ConnectException('Connection error', $connectRequest);

    yield 'ConnectException without response' => [
      $connectException,
      'Connection error',
    ];
  }

  /**
   * @covers ::send
   *
   * @dataProvider exceptionHandlingProvider
   */
  public function testSendCatchesExceptions(\Exception $exception, $expected): void {
    $this->httpClient->expects($this->once())
      ->method('request')
      ->willThrowException($exception);

    $result = $this->invokeProtectedMethod($this->cohesionApiClient, 'send', ['GET', '/test']);

    if (is_array($expected)) {
      $this->assertArrayHasKey('error', $result['data']);
      $this->assertEquals($expected['error'], $result['data']['error']);
    }
    else {
      $this->assertArrayHasKey('exception_message', $result);
      $this->assertStringContainsString($expected, $result['exception_message']);
    }
  }

  /**
   * @covers ::send
   * Tests request with compression enabled.
   */
  public function testSendWithCompression(): void {
    // Configure the cohesion settings to enable compression.
    $this->cohesionConfig = $this->createMock(ImmutableConfig::class);
    $this->cohesionConfig->expects($this->any())
      ->method('get')
      ->willReturnMap([
        ['compress_outbound_request', TRUE],
        ['api_key', 'test_api_key'],
        ['organization_key', 'test_org_key'],
      ]);

    $this->configFactory = $this->createMock(ConfigFactoryInterface::class);
    $this->configFactory->expects($this->any())
      ->method('get')
      ->with('cohesion.settings')
      ->willReturn($this->cohesionConfig);

    \Drupal::getContainer()->set('config.factory', $this->configFactory);

    $response = new Response(200, [], json_encode(['status' => 'success']));

    $this->httpClient->expects($this->once())
      ->method('request')
      ->with(
        $this->equalTo('POST'),
        $this->equalTo('https://api.cohesion.net/test'),
        $this->callback(function ($options) {
          return isset($options['headers']['Content-Encoding'])
            && $options['headers']['Content-Encoding'] === 'gzip';
        })
      )
      ->willReturn($response);

    $result = $this->invokeProtectedMethod($this->cohesionApiClient, 'send', ['POST', '/test', ['data' => 'test']]);

    $this->assertEquals(['status' => 'success'], $result['data']);
  }

  /**
   * Data provider for auth error flag tests.
   *
   * @return iterable
   *   Test cases with named keys for auth error scenarios.
   */
  public static function authErrorFlagProvider(): iterable {
    yield '401 Unauthorized sets is_auth_error flag' => [
      401,
      ['error' => 'Unauthorized'],
      TRUE,
      TRUE,
      'error_response',
    ];

    yield '403 Forbidden sets is_auth_error flag' => [
      403,
      ['error' => 'Forbidden'],
      TRUE,
      TRUE,
      'error_response',
    ];

    yield '500 Server Error does not set is_auth_error flag' => [
      500,
      ['error' => 'Server error'],
      TRUE,
      FALSE,
      'error_response',
    ];

    yield '200 Success does not set is_auth_error flag' => [
      200,
      ['status' => 'success'],
      FALSE,
      FALSE,
      'success_response',
    ];
  }

  /**
   * @covers ::send
   *
   * @dataProvider authErrorFlagProvider
   */
  public function testSendAuthErrorFlag(
    int $statusCode,
    array $responseBody,
    bool $throwsException,
    bool $expectedAuthError,
    string $scenario
  ): void {
    if ($throwsException) {
      $response = new Response($statusCode, [], json_encode($responseBody));
      $request = new Request('GET', '/test');
      $exception = new RequestException('Error', $request, $response);

      $this->httpClient->expects($this->once())
        ->method('request')
        ->willThrowException($exception);
    }
    else {
      $response = new Response($statusCode, [], json_encode($responseBody));

      $this->httpClient->expects($this->once())
        ->method('request')
        ->willReturn($response);
    }

    $result = $this->invokeProtectedMethod($this->cohesionApiClient, 'send', ['GET', '/test']);

    $this->assertArrayHasKey('is_auth_error', $result);
    $this->assertSame($expectedAuthError, $result['is_auth_error']);
    $this->assertEquals($statusCode, $result['code']);
  }

  /**
   * Helper method to invoke protected/private methods.
   *
   * @param object $object
   *   Object containing the method.
   * @param string $methodName
   *   Method name to call.
   * @param array $parameters
   *   Parameters to pass to the method.
   *
   * @return mixed
   *   Method result.
   */
  protected function invokeProtectedMethod($object, $methodName, array $parameters = []) {
    $reflection = new \ReflectionClass(get_class($object));
    $method = $reflection->getMethod($methodName);
    $method->setAccessible(TRUE);

    return $method->invokeArgs($object, $parameters);
  }

}
