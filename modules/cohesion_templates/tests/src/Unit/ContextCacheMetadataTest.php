<?php

namespace Drupal\Tests\cohesion_templates\Unit;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion_templates\ContextCacheMetadata;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Drupal\Core\KeyValueStore\KeyValueStoreInterface;
use Drupal\Tests\UnitTestCase;

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

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal()
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

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal()
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

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal()
    );

    $entity = $this->prophesize(CohesionConfigEntityBase::class);
    $entity->get('twig_template')->willReturn(NULL);

    $result = $service->extractContextNames($entity->reveal());

    $this->assertEquals([], $result);
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

    $service = new ContextCacheMetadataTestable(
      $moduleHandler->reveal(),
      $keyValueFactory->reveal()
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
  ) {
    $this->keyValue = $keyValue;
    if ($moduleHandler->moduleExists('context')) {
      $this->contexts = [];
    }
  }

}
