<?php

namespace Drupal\Tests\cohesion_templates\Unit;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion_templates\ContextCacheMetadata;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests for ContextCacheMetadata service.
 *
 * @group Cohesion
 * @coversDefaultClass \Drupal\cohesion_templates\ContextCacheMetadata
 */
class ContextCacheMetadataTest extends UnitTestCase {

  /**
   * Tests that extractContextNames memoizes key-value reads.
   *
   * When called multiple times with entities using the same twig_template,
   * the key-value store should only be queried once per unique template.
   *
   * @covers ::extractContextNames
   */
  public function testExtractContextNamesMemoizesKeyValueReads(): void {
    $templateSlug = 'component--coh-test-component';

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore->get($templateSlug)
      ->shouldBeCalledTimes(1)
      ->willReturn([
        'contexts' => ['context:user_role', 'context:node_type'],
      ]);

    $keyValueFactory = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore->reveal());

    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $moduleHandler->moduleExists('context')->willReturn(TRUE);

    $requestStack = new RequestStack();
    $requestStack->push(new Request());

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal(),
      $requestStack
    );

    $entity1 = $this->prophesize(CohesionConfigEntityBase::class);
    $entity1->get('twig_template')->willReturn($templateSlug);

    $entity2 = $this->prophesize(CohesionConfigEntityBase::class);
    $entity2->get('twig_template')->willReturn($templateSlug);

    $entity3 = $this->prophesize(CohesionConfigEntityBase::class);
    $entity3->get('twig_template')->willReturn($templateSlug);

    $service->extractContextNames($entity1->reveal());
    $service->extractContextNames($entity2->reveal());
    $service->extractContextNames($entity3->reveal());
  }

  /**
   * Tests that different templates are each queried once.
   *
   * @covers ::extractContextNames
   */
  public function testExtractContextNamesQueriesEachTemplateOnce(): void {
    $template1 = 'component--coh-component-a';
    $template2 = 'component--coh-component-b';

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore->get($template1)
      ->shouldBeCalledTimes(1)
      ->willReturn(['contexts' => ['context:user_role']]);
    $keyValueStore->get($template2)
      ->shouldBeCalledTimes(1)
      ->willReturn(['contexts' => ['context:node_type']]);

    $keyValueFactory = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore->reveal());

    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $moduleHandler->moduleExists('context')->willReturn(TRUE);

    $requestStack = new RequestStack();
    $requestStack->push(new Request());

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal(),
      $requestStack
    );

    $entityA1 = $this->prophesize(CohesionConfigEntityBase::class);
    $entityA1->get('twig_template')->willReturn($template1);

    $entityA2 = $this->prophesize(CohesionConfigEntityBase::class);
    $entityA2->get('twig_template')->willReturn($template1);

    $entityB1 = $this->prophesize(CohesionConfigEntityBase::class);
    $entityB1->get('twig_template')->willReturn($template2);

    $entityB2 = $this->prophesize(CohesionConfigEntityBase::class);
    $entityB2->get('twig_template')->willReturn($template2);

    $service->extractContextNames($entityA1->reveal());
    $service->extractContextNames($entityB1->reveal());
    $service->extractContextNames($entityA2->reveal());
    $service->extractContextNames($entityB2->reveal());
  }

  /**
   * Tests that NULL template values do not cause key-value lookups.
   *
   * @covers ::extractContextNames
   */
  public function testExtractContextNamesHandlesNullTemplate(): void {
    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore->get(\Prophecy\Argument::any())->shouldNotBeCalled();

    $keyValueFactory = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore->reveal());

    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $moduleHandler->moduleExists('context')->willReturn(TRUE);

    $requestStack = new RequestStack();
    $requestStack->push(new Request());

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal(),
      $requestStack
    );

    $entity = $this->prophesize(CohesionConfigEntityBase::class);
    $entity->get('twig_template')->willReturn(NULL);

    $result = $service->extractContextNames($entity->reveal());

    $this->assertEquals([], $result);
  }

  /**
   * Tests that cache is retained across multiple calls in no-request scenario.
   *
   * When there is no active request (CLI, cron, queue rendering), the cache
   * should be retained across multiple calls, not cleared on every call.
   *
   * @covers ::extractContextNames
   * @covers ::ensureRequestCacheIsValid
   */
  public function testExtractContextNamesMemoizesWithoutRequest(): void {
    $templateSlug = 'component--coh-test-component';

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore->get($templateSlug)
      ->shouldBeCalledTimes(1)
      ->willReturn(['contexts' => ['context:user_role']]);

    $keyValueFactory = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore->reveal());

    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $moduleHandler->moduleExists('context')->willReturn(TRUE);

    $requestStack = new RequestStack();
    // No request pushed - simulates CLI, cron, or queue rendering

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal(),
      $requestStack
    );

    $entity = $this->prophesize(CohesionConfigEntityBase::class);
    $entity->get('twig_template')->willReturn($templateSlug);

    // Multiple calls without a request should use the same cached value
    // The key-value store should only be hit once, not on every call
    $result1 = $service->extractContextNames($entity->reveal());
    $result2 = $service->extractContextNames($entity->reveal());
    $result3 = $service->extractContextNames($entity->reveal());

    $this->assertEquals($result1, $result2);
    $this->assertEquals($result2, $result3);
    $this->assertContains('user_role', $result1);
  }

  /**
   * Tests that cache is cleared when a new request is detected.
   *
   * Verifies the fix for stale metadata being cached across requests.
   * When the current request changes (different request object), the metadata
   * cache should be cleared and fresh data should be fetched.
   *
   * @covers ::extractContextNames
   * @covers ::ensureRequestCacheIsValid
   */
  public function testExtractContextNamesClearsMetadataCacheOnNewRequest(): void {
    $templateSlug = 'component--coh-test-component';

    // First mock for request 1
    $keyValueStore1 = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore1->get($templateSlug)
      ->shouldBeCalledTimes(1)
      ->willReturn(['contexts' => ['context:user_role']]);

    $keyValueFactory1 = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory1->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore1->reveal());

    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $moduleHandler->moduleExists('context')->willReturn(TRUE);

    $requestStack = new RequestStack();
    $request1 = new Request();
    $requestStack->push($request1);

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory1->reveal(),
      $requestStack
    );

    $entity = $this->prophesize(CohesionConfigEntityBase::class);
    $entity->get('twig_template')->willReturn($templateSlug);

    // First call with request1 - fetches and caches metadata
    $result1 = $service->extractContextNames($entity->reveal());
    $this->assertContains('user_role', $result1);

    // Create new mock for request 2 with different return value
    $keyValueStore2 = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore2->get($templateSlug)
      ->shouldBeCalledTimes(1)
      ->willReturn(['contexts' => ['context:node_type']]);

    $keyValueFactory2 = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory2->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore2->reveal());

    // Replace the service's keyValue with the new factory
    $service->setKeyValueFactory($keyValueFactory2->reveal());

    // Simulate a new request by pushing a new request onto the stack
    $request2 = new Request();
    $requestStack->push($request2);

    // Second call with request2 - should detect new request, clear cache,
    // and fetch fresh metadata with the new mock
    $result2 = $service->extractContextNames($entity->reveal());
    $this->assertContains('node_type', $result2);
  }

  /**
   * Tests that empty metadata returns empty context array.
   *
   * @covers ::extractContextNames
   */
  public function testExtractContextNamesHandlesEmptyMetadata(): void {
    $templateSlug = 'component--coh-empty-component';

    $keyValueStore = $this->prophesize(KeyValueStoreInterface::class);
    $keyValueStore->get($templateSlug)
      ->shouldBeCalledTimes(1)
      ->willReturn(NULL);

    $keyValueFactory = $this->prophesize(KeyValueFactoryInterface::class);
    $keyValueFactory->get(ContextCacheMetadata::TEMPLATE_METADATA_STORE)
      ->willReturn($keyValueStore->reveal());

    $moduleHandler = $this->prophesize(ModuleHandlerInterface::class);
    $moduleHandler->moduleExists('context')->willReturn(TRUE);

    $requestStack = new RequestStack();
    $requestStack->push(new Request());

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal(),
      $requestStack
    );
    $entity = $this->prophesize(CohesionConfigEntityBase::class);
    $entity->get('twig_template')->willReturn($templateSlug);

    $result = $service->extractContextNames($entity->reveal());

    $this->assertEquals([], $result);
  }

}

/**
 * Testable subclass that avoids calling \Drupal::service() in constructor.
 */
class ContextCacheMetadataTestable extends ContextCacheMetadata {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    KeyValueFactoryInterface $keyValue,
    RequestStack $requestStack,
  ) {
    $this->keyValue = $keyValue;
    $this->requestStack = $requestStack;
    if ($moduleHandler->moduleExists('context')) {
      $this->contexts = [];
    }
  }

  /**
   * Allows tests to update the keyValue factory.
   */
  public function setKeyValueFactory(KeyValueFactoryInterface $keyValue): void {
    $this->keyValue = $keyValue;
  }

}

