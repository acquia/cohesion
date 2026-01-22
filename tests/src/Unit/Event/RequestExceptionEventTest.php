<?php

namespace Drupal\Tests\cohesion\Unit\Event;

use Drupal\cohesion\Event\RequestExceptionEvent;
use Drupal\Tests\UnitTestCase;

/**
 * @coversDefaultClass \Drupal\cohesion\Event\RequestExceptionEvent
 * @group cohesion
 */
class RequestExceptionEventTest extends UnitTestCase {

  /**
   * @var \Drupal\cohesion\Event\RequestExceptionEvent
   */
  protected $event;

  /**
   * Test data for the event.
   */
  protected $testData = [
    'method' => 'POST',
    'uri' => '/api/test',
    'payload' => ['test' => 'data'],
    'statusCode' => 400,
    'requestId' => 'test-request-id',
    'exceptionMessage' => 'Test exception message',
    'responseData' => ['error' => 'Test error'],
    'requestDuration' => 1.5,
    'entityId' => '123',
    'entityType' => 'node',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Create the event with test data
    $this->event = new RequestExceptionEvent(
      $this->testData['method'],
      $this->testData['uri'],
      $this->testData['payload'],
      $this->testData['statusCode'],
      $this->testData['requestId'],
      $this->testData['exceptionMessage'],
      $this->testData['responseData'],
      $this->testData['requestDuration'],
      $this->testData['entityId'],
      $this->testData['entityType']
    );
  }

  /**
   * Tests the getMethod method.
   *
   * @covers ::getMethod
   */
  public function testGetMethod() {
    $this->assertEquals($this->testData['method'], $this->event->getMethod());
  }

  /**
   * Tests the getUri method.
   *
   * @covers ::getUri
   */
  public function testGetUri() {
    $this->assertEquals($this->testData['uri'], $this->event->getUri());
  }

  /**
   * Tests the getPayload method.
   *
   * @covers ::getPayload
   */
  public function testGetPayload() {
    $this->assertEquals($this->testData['payload'], $this->event->getPayload());
  }

  /**
   * Tests the getStatusCode method.
   *
   * @covers ::getStatusCode
   */
  public function testGetStatusCode() {
    $this->assertEquals($this->testData['statusCode'], $this->event->getStatusCode());
  }

  /**
   * Tests the getRequestId method.
   *
   * @covers ::getRequestId
   */
  public function testGetRequestId() {
    $this->assertEquals($this->testData['requestId'], $this->event->getRequestId());
  }

  /**
   * Tests the getExceptionMessage method.
   *
   * @covers ::getExceptionMessage
   */
  public function testGetExceptionMessage() {
    $this->assertEquals($this->testData['exceptionMessage'], $this->event->getExceptionMessage());
  }

  /**
   * Tests the getResponseData method.
   *
   * @covers ::getResponseData
   */
  public function testGetResponseData() {
    $this->assertEquals($this->testData['responseData'], $this->event->getResponseData());
  }

  /**
   * Tests the getRequestDuration method.
   *
   * @covers ::getRequestDuration
   */
  public function testGetRequestDuration() {
    $this->assertEquals($this->testData['requestDuration'], $this->event->getRequestDuration());
  }

  /**
   * Tests the getEntityId method.
   *
   * @covers ::getEntityId
   */
  public function testGetEntityId() {
    $this->assertEquals($this->testData['entityId'], $this->event->getEntityId());
  }

  /**
   * Tests the getEntityType method.
   *
   * @covers ::getEntityType
   */
  public function testGetEntityType() {
    $this->assertEquals($this->testData['entityType'], $this->event->getEntityType());
  }

  /**
   * Tests the event with null entity values.
   *
   * @covers ::getEntityId
   * @covers ::getEntityType
   */
  public function testNullEntityValues() {
    $event = new RequestExceptionEvent(
      $this->testData['method'],
      $this->testData['uri'],
      $this->testData['payload'],
      $this->testData['statusCode'],
      $this->testData['requestId'],
      $this->testData['exceptionMessage'],
      $this->testData['responseData'],
      $this->testData['requestDuration']
      // No entityId or entityType provided (should default to NULL)
    );

    $this->assertNull($event->getEntityId());
    $this->assertNull($event->getEntityType());
  }

  /**
   * Tests the event with null status code.
   *
   * @covers ::getStatusCode
   */
  public function testNullStatusCode() {
    $event = new RequestExceptionEvent(
      $this->testData['method'],
      $this->testData['uri'],
      $this->testData['payload'],
      null,
      $this->testData['requestId'],
      $this->testData['exceptionMessage'],
      $this->testData['responseData'],
      $this->testData['requestDuration']
    );

    $this->assertNull($event->getStatusCode());
  }
}
