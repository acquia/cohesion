<?php

namespace Drupal\cohesion\Event;

/**
 *
 */
class RequestExceptionEvent {

  public function __construct(
    private string $method,
    private string $uri,
    private mixed $payload,
    private ?int $statusCode,
    private string $requestId,
    private string $exceptionMessage,
    private mixed $responseData,
    private float $requestDuration,
    private ?string $entityId = NULL,
    private ?string $entityType = NULL,
  ) {
  }

  public function getMethod(): string {
    return $this->method;
  }

  public function getUri(): string {
    return $this->uri;
  }

  public function getPayload(): mixed {
    return $this->payload;
  }

  public function getStatusCode(): ?int {
    return $this->statusCode;
  }

  public function getRequestId(): string {
    return $this->requestId;
  }

  public function getExceptionMessage(): string {
    return $this->exceptionMessage;
  }

  public function getResponseData(): mixed {
    return $this->responseData;
  }

  public function getRequestDuration(): float {
    return $this->requestDuration;
  }

  public function getEntityId(): ?string {
    return $this->entityId;
  }

  public function getEntityType(): ?string {
    return $this->entityType;
  }

}
