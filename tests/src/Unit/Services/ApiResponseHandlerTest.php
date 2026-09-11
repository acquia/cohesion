<?php

namespace Drupal\Tests\cohesion\Unit\Services;

use Drupal\cohesion\Services\ApiResponseHandler;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Tests\UnitTestCase;
use Psr\Log\LoggerInterface;

/**
 * @coversDefaultClass \Drupal\cohesion\Services\ApiResponseHandler
 * @group Cohesion
 */
class ApiResponseHandlerTest extends UnitTestCase {

  /**
   * The ApiResponseHandler instance to test.
   *
   * @var \Drupal\cohesion\Services\ApiResponseHandler
   */
  protected $apiResponseHandler;

  /**
   * The mocked logger.
   *
   * @var \Psr\Log\LoggerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $logger;

  /**
   * The mocked messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $messenger;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->logger = $this->createMock(LoggerInterface::class);

    $loggerFactory = $this->createMock(LoggerChannelFactoryInterface::class);
    $loggerFactory->expects($this->any())
      ->method('get')
      ->with('cohesion')
      ->willReturn($this->logger);

    $this->messenger = $this->createMock(MessengerInterface::class);

    // Create a mock translation that returns actual strings
    $stringTranslation = $this->getMockBuilder(TranslationInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    // Mock the translateString method to return a rendered string.
    $stringTranslation->method('translateString')
      ->willReturnCallback(function (\Drupal\Core\StringTranslation\TranslatableMarkup $markupObject, array $options = []) {
        return (string) new \Drupal\Component\Render\FormattableMarkup($markupObject->getUntranslatedString(), $markupObject->getArguments());
      });

    $this->apiResponseHandler = new ApiResponseHandler(
      $loggerFactory,
      $this->messenger,
      $stringTranslation
    );
  }

  /**
   * Data provider for isAuthError tests.
   *
   * @return iterable
   *   Test cases with named keys.
   */
  public static function isAuthErrorProvider(): iterable {
    yield 'is_auth_error flag is set returns true' => [
      ['is_auth_error' => TRUE, 'code' => 401],
      TRUE,
    ];

    yield 'is_auth_error flag not set returns false' => [
      ['code' => 200],
      FALSE,
    ];

    yield 'is_auth_error explicitly false returns false' => [
      ['is_auth_error' => FALSE, 'code' => 500],
      FALSE,
    ];

    yield 'empty response returns false' => [
      [],
      FALSE,
    ];
  }

  /**
   * @covers ::isAuthError
   *
   * @dataProvider isAuthErrorProvider
   */
  public function testIsAuthError(array $response, bool $expected): void {
    $this->assertSame($expected, $this->apiResponseHandler->isAuthError($response));
  }

  /**
   * Data provider for handleBatchAuthError tests - non-error cases.
   *
   * @return iterable
   *   Test cases where no auth error is present.
   */
  public static function noAuthErrorProvider(): iterable {
    yield 'valid response returns false and sets no error' => [
      ['is_auth_error' => FALSE, 'code' => 200],
      ['results' => []],
    ];
  }

  /**
   * @covers ::handleBatchAuthError
   *
   * @dataProvider noAuthErrorProvider
   */
  public function testHandleBatchAuthErrorNoError(array $response, array $context): void {
    $this->logger->expects($this->never())
      ->method('error');

    $this->messenger->expects($this->never())
      ->method('addError');

    $result = $this->apiResponseHandler->handleBatchAuthError($response, $context);

    $this->assertFalse($result);
    $this->assertArrayNotHasKey('error', $context['results']);
    $this->assertArrayNotHasKey('auth_error', $context['results']);
  }

  /**
   * Data provider for handleBatchAuthError tests - error cases.
   *
   * @return iterable
   *   Test cases where auth error is present.
   */
  public static function authErrorProvider(): iterable {
    yield '401 error with request_id' => [
      [
        'is_auth_error' => TRUE,
        'code' => 401,
        'request_id' => 'test-request-id-401',
      ],
      ['results' => []],
      '401',
      'test-request-id-401',
    ];

    yield '403 error with request_id' => [
      [
        'is_auth_error' => TRUE,
        'code' => 403,
        'request_id' => 'test-request-id-403',
      ],
      ['results' => []],
      '403',
      'test-request-id-403',
    ];

    yield '401 error without request_id shows unknown' => [
      [
        'is_auth_error' => TRUE,
        'code' => 401,
      ],
      ['results' => []],
      '401',
      'unknown',
    ];
  }

  /**
   * @covers ::handleBatchAuthError
   *
   * @dataProvider authErrorProvider
   */
  public function testHandleBatchAuthErrorWithError(
    array $response,
    array $context,
    string $expectedCode,
    string $expectedRequestId
  ): void {
    $this->logger->expects($this->once())
      ->method('error');

    $this->messenger->expects($this->once())
      ->method('addError');

    $result = $this->apiResponseHandler->handleBatchAuthError($response, $context);

    $this->assertTrue($result);
    $this->assertArrayHasKey('error', $context['results']);
    $this->assertArrayHasKey('auth_error', $context['results']);
    $this->assertTrue($context['results']['auth_error']);

    $errorMessage = (string) $context['results']['error'];
    $this->assertStringContainsString($expectedCode, $errorMessage);
    $this->assertStringContainsString($expectedRequestId, $errorMessage);
  }

  /**
   * @covers ::handleBatchAuthError
   * Tests that error message contains actionable instructions.
   */
  public function testHandleBatchAuthErrorMessageContainsInstructions(): void {
    $response = [
      'is_auth_error' => TRUE,
      'code' => 401,
      'request_id' => 'test-uuid',
    ];
    $context = ['results' => []];

    $this->apiResponseHandler->handleBatchAuthError($response, $context);

    $errorMessage = (string) $context['results']['error'];
    $this->assertStringContainsString('API key', $errorMessage);
    $this->assertStringContainsString('Organization key', $errorMessage);
    $this->assertStringContainsString('/admin/cohesion/configuration/account-settings', $errorMessage);
    $this->assertStringContainsString('rebuild has been stopped', $errorMessage);
  }

  /**
   * @covers ::getAuthErrorCodes
   * Tests that getAuthErrorCodes returns expected codes.
   */
  public function testGetAuthErrorCodesReturnsExpectedCodes(): void {
    $codes = $this->apiResponseHandler->getAuthErrorCodes();

    $this->assertIsArray($codes);
    $this->assertContains(401, $codes);
    $this->assertContains(403, $codes);
    $this->assertCount(2, $codes);
  }

}
