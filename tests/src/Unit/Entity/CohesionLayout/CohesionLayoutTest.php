<?php

namespace Drupal\Tests\cohesion\Unit\Entity\CohesionLayout;

use Drupal\cohesion\EntityUpdateManager;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheTagsInvalidatorInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;

/**
 * Unit tests for CohesionLayout entity.
 *
 * @coversDefaultClass \Drupal\cohesion_elements\Entity\CohesionLayout
 * @group Cohesion
 */
class CohesionLayoutTest extends UnitTestCase {

  /**
   * {@inheritdoc}
   */
  protected function tearDown(): void {
    parent::tearDown();
    $container = new ContainerBuilder();
    \Drupal::setContainer($container);
  }

  /**
   * Sets up a mock container with required services for preSave tests.
   *
   * @param \Drupal\cohesion\EntityUpdateManager $entityUpdateManager
   *   The mocked entity update manager.
   * @param \Drupal\Core\Messenger\MessengerInterface|null $messenger
   *   Optional mocked messenger.
   *
   * @return \Drupal\Core\DependencyInjection\ContainerBuilder
   *   The configured container.
   */
  protected function setUpPreSaveContainer($entityUpdateManager, $messenger = NULL): ContainerBuilder {
    $entityTypeManager = $this->prophesize(EntityTypeManagerInterface::class);

    $container = new ContainerBuilder();
    $container->set('cohesion.entity_update_manager', $entityUpdateManager);
    $container->set('entity_type.manager', $entityTypeManager->reveal());

    if ($messenger) {
      $container->set('messenger', $messenger);
    }

    \Drupal::setContainer($container);
    return $container;
  }

  /**
   * Creates a mock entity type for CohesionLayout.
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface
   *   The mocked entity type.
   */
  protected function createMockEntityType(): EntityTypeInterface {
    $entityType = $this->prophesize(EntityTypeInterface::class);
    $entityType->getKeys()->willReturn([
      'id' => 'id',
      'uuid' => 'uuid',
      'revision' => 'revision',
      'langcode' => 'langcode',
    ]);
    $entityType->isRevisionable()->willReturn(TRUE);
    $entityType->id()->willReturn('cohesion_layout');
    $entityType->getBundleEntityType()->willReturn(NULL);
    $entityType->getBundleOf()->willReturn(NULL);
    $entityType->getConstraints()->willReturn([]);

    return $entityType->reveal();
  }

  /**
   * Tests that processApiResponse() does nothing when given an empty array.
   *
   * @covers ::processApiResponse
   */
  public function testProcessApiResponseWithEmptyArrayDoesNothing(): void {
    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['set'])
      ->getMock();

    $layout->expects($this->never())
      ->method('set');

    $layout->processApiResponse([]);
  }

  /**
   * Tests that processApiResponse() does nothing when given null.
   *
   * @covers ::processApiResponse
   */
  public function testProcessApiResponseWithNullDoesNothing(): void {
    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['set'])
      ->getMock();

    $layout->expects($this->never())
      ->method('set');

    $layout->processApiResponse(NULL);
  }

  /**
   * Tests that processApiResponse() saves template when given valid data.
   *
   * @covers ::processApiResponse
   */
  public function testProcessApiResponseSavesTemplateWithValidData(): void {
    $cohesionUtils = $this->prophesize(CohesionUtils::class);
    $cohesionUtils->getCohesionTemplateOnlyEnabledThemes()->willReturn([]);

    $cacheTagsInvalidator = $this->prophesize(CacheTagsInvalidatorInterface::class);
    $cacheTagsInvalidator->invalidateTags(['theme_registry'])->shouldBeCalled();

    $container = new ContainerBuilder();
    $container->set('cohesion.utils', $cohesionUtils->reveal());
    $container->set('cache_tags.invalidator', $cacheTagsInvalidator->reveal());
    \Drupal::setContainer($container);

    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods(['set', 'getApiPluginInstance'])
      ->getMock();

    $layout->expects($this->exactly(2))
      ->method('set')
      ->willReturnCallback(function ($field, $value) {
        static $calls = [];
        $calls[] = [$field, $value];

        if (count($calls) === 2) {
          $this->assertEquals('styles', $calls[0][0]);
          $this->assertEquals('[]', $calls[0][1]);
          $this->assertEquals('template', $calls[1][0]);
          $decoded = Json::decode($calls[1][1]);
          $this->assertArrayHasKey('cohesion_theme', $decoded);
          $this->assertStringContainsString('attach_library', $decoded['cohesion_theme']);
          $this->assertStringContainsString('coh-container', $decoded['cohesion_theme']);
        }
      });

    $template = <<<'TWIG'
{{ attach_library('cohesion/global_libraries.responsiveJs') }}
<div class="coh-container coh-ce-a1b2c3d4" >
  {{ content }}
</div>
{% if content is defined %}{% set catch_cache = content|render %}{% endif %}
TWIG;

    $responseData = [
      [
        'themeName' => 'cohesion_theme',
        'css' => Json::encode([
          'styles' => [
            'added' => [],
            'updated' => [],
            'deleted' => [],
          ],
        ]),
        'template' => $template,
      ],
    ];

    $layout->processApiResponse($responseData);
  }

  /**
   * Data provider for TMGMT translation cache tests.
   *
   * @return array
   *   Test cases with langcode, entityId, cacheEntityId, cachedLangcode,
   *   and expectedApply.
   */
  public static function tmgmtTranslationCacheDataProvider(): array {
    return [
      'English translation applied' => [
        'langcode' => 'en',
        'entityId' => 123,
        'cacheEntityId' => 123,
        'cachedLangcode' => 'en',
        'expectedApply' => TRUE,
      ],
      'French translation applied' => [
        'langcode' => 'fr',
        'entityId' => 123,
        'cacheEntityId' => 123,
        'cachedLangcode' => 'fr',
        'expectedApply' => TRUE,
      ],
      'German translation applied' => [
        'langcode' => 'de',
        'entityId' => 456,
        'cacheEntityId' => 456,
        'cachedLangcode' => 'de',
        'expectedApply' => TRUE,
      ],
      'Spanish translation applied' => [
        'langcode' => 'es',
        'entityId' => 789,
        'cacheEntityId' => 789,
        'cachedLangcode' => 'es',
        'expectedApply' => TRUE,
      ],
      'Cache exists for different language - not applied' => [
        'langcode' => 'en',
        'entityId' => 123,
        'cacheEntityId' => 123,
        'cachedLangcode' => 'fr',
        'expectedApply' => FALSE,
      ],
      'Cache exists for different entity - not applied' => [
        'langcode' => 'en',
        'entityId' => 999,
        'cacheEntityId' => 123,
        'cachedLangcode' => 'en',
        'expectedApply' => FALSE,
      ],
      'New entity (no id) - not applied' => [
        'langcode' => 'en',
        'entityId' => NULL,
        'cacheEntityId' => 123,
        'cachedLangcode' => 'en',
        'expectedApply' => FALSE,
      ],
    ];
  }

  /**
   * Tests preSave() TMGMT translation cache behavior.
   *
   * @param string $langcode
   *   The language code of the entity being saved.
   * @param int|null $entityId
   *   The entity ID, or NULL for new entities.
   * @param int $cacheEntityId
   *   The entity ID used as the cache key.
   * @param string $cachedLangcode
   *   The language code used in the translation cache.
   * @param bool $expectedApply
   *   Whether the cached translation should be applied.
   *
   * @covers ::preSave
   * @dataProvider tmgmtTranslationCacheDataProvider
   */
  public function testPreSaveTmgmtTranslationCache(string $langcode, ?int $entityId, int $cacheEntityId, string $cachedLangcode, bool $expectedApply): void {
    $cachedJson = '{"canvas":[{"type":"translated_component","lang":"' . $cachedLangcode . '"}]}';

    $translation_cache = &drupal_static('cohesion_layout_tmgmt_translations', []);
    $translation_cache[$cacheEntityId][$cachedLangcode] = $cachedJson;

    $entityUpdateManager = $this->prophesize(EntityUpdateManager::class);
    $entityUpdateManager->apply(Argument::any())->shouldBeCalled();

    $this->setUpPreSaveContainer($entityUpdateManager->reveal());

    $storage = $this->prophesize(EntityStorageInterface::class);
    $entityType = $this->createMockEntityType();

    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'id',
        'language',
        'setJsonValue',
        'get',
        'process',
        'getEntityType',
      ])
      ->getMock();

    $layout->callApi = FALSE;

    $layout->expects($this->any())
      ->method('id')
      ->willReturn($entityId);

    $layout->expects($this->any())
      ->method('getEntityType')
      ->willReturn($entityType);

    $language = new Language(['id' => $langcode]);
    $layout->expects($this->any())
      ->method('language')
      ->willReturn($language);

    if ($expectedApply) {
      $layout->expects($this->once())
        ->method('setJsonValue')
        ->with($cachedJson);
    }
    else {
      $layout->expects($this->never())
        ->method('setJsonValue');
    }

    $stylesField = $this->prophesize(FieldItemListInterface::class);
    $stylesField->getValue()->willReturn([]);

    $templateField = $this->prophesize(FieldItemListInterface::class);
    $templateField->getValue()->willReturn([]);

    $layout->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($field) use ($stylesField, $templateField) {
        if ($field === 'styles') {
          return $stylesField->reveal();
        }
        if ($field === 'template') {
          return $templateField->reveal();
        }
        return NULL;
      });

    $layout->preSave($storage->reveal());

    drupal_static_reset('cohesion_layout_tmgmt_translations');
  }

  /**
   * Tests preSave() calls process() when callApi is TRUE.
   *
   * @covers ::preSave
   */
  public function testPreSaveCallsProcessWhenCallApiEnabled(): void {
    $entityUpdateManager = $this->prophesize(EntityUpdateManager::class);
    $entityUpdateManager->apply(Argument::any())->shouldBeCalled();

    $this->setUpPreSaveContainer($entityUpdateManager->reveal());

    $storage = $this->prophesize(EntityStorageInterface::class);
    $entityType = $this->createMockEntityType();

    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'id',
        'language',
        'get',
        'process',
        'getEntityType',
      ])
      ->getMock();

    $layout->callApi = TRUE;

    $layout->expects($this->any())
      ->method('id')
      ->willReturn(NULL);

    $layout->expects($this->any())
      ->method('getEntityType')
      ->willReturn($entityType);

    $language = new Language(['id' => 'en']);
    $layout->expects($this->any())
      ->method('language')
      ->willReturn($language);

    $stylesField = $this->prophesize(FieldItemListInterface::class);
    $stylesField->getValue()->willReturn([]);

    $templateField = $this->prophesize(FieldItemListInterface::class);
    $templateField->getValue()->willReturn([]);

    $layout->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($field) use ($stylesField, $templateField) {
        if ($field === 'styles') {
          return $stylesField->reveal();
        }
        if ($field === 'template') {
          return $templateField->reveal();
        }
        return NULL;
      });

    $layout->expects($this->once())
      ->method('process')
      ->willReturn(FALSE);

    $layout->preSave($storage->reveal());
  }

  /**
   * Tests preSave() restores styles/template on API error.
   *
   * @covers ::preSave
   */
  public function testPreSaveRestoresDataOnApiError(): void {
    $originalStyles = ['value' => 'original_styles'];
    $originalTemplate = ['value' => 'original_template'];

    $entityUpdateManager = $this->prophesize(EntityUpdateManager::class);
    $entityUpdateManager->apply(Argument::any())->shouldBeCalled();

    $messenger = $this->prophesize(MessengerInterface::class);
    $messenger->addMessage('API Error occurred', 'error')->shouldBeCalled();

    $this->setUpPreSaveContainer($entityUpdateManager->reveal(), $messenger->reveal());

    $storage = $this->prophesize(EntityStorageInterface::class);
    $entityType = $this->createMockEntityType();

    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'id',
        'language',
        'get',
        'set',
        'process',
        'getEntityType',
      ])
      ->getMock();

    $layout->callApi = TRUE;

    $layout->expects($this->any())
      ->method('id')
      ->willReturn(NULL);

    $layout->expects($this->any())
      ->method('getEntityType')
      ->willReturn($entityType);

    $language = new Language(['id' => 'en']);
    $layout->expects($this->any())
      ->method('language')
      ->willReturn($language);

    $stylesField = $this->prophesize(FieldItemListInterface::class);
    $stylesField->getValue()->willReturn($originalStyles);

    $templateField = $this->prophesize(FieldItemListInterface::class);
    $templateField->getValue()->willReturn($originalTemplate);

    $layout->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($field) use ($stylesField, $templateField) {
        if ($field === 'styles') {
          return $stylesField->reveal();
        }
        if ($field === 'template') {
          return $templateField->reveal();
        }
        return NULL;
      });

    $layout->expects($this->once())
      ->method('process')
      ->willReturn('API Error occurred');

    $setCalls = [];
    $layout->expects($this->exactly(2))
      ->method('set')
      ->willReturnCallback(function ($field, $value) use (&$setCalls) {
        $setCalls[] = [$field, $value];
      });

    $layout->preSave($storage->reveal());

    $this->assertEquals('styles', $setCalls[0][0]);
    $this->assertEquals($originalStyles, $setCalls[0][1]);
    $this->assertEquals('template', $setCalls[1][0]);
    $this->assertEquals($originalTemplate, $setCalls[1][1]);
  }

  /**
   * Tests preSave() skips process() when callApi is FALSE.
   *
   * @covers ::preSave
   */
  public function testPreSaveSkipsProcessWhenCallApiDisabled(): void {
    $entityUpdateManager = $this->prophesize(EntityUpdateManager::class);
    $entityUpdateManager->apply(Argument::any())->shouldBeCalled();

    $this->setUpPreSaveContainer($entityUpdateManager->reveal());

    $storage = $this->prophesize(EntityStorageInterface::class);
    $entityType = $this->createMockEntityType();

    $layout = $this->getMockBuilder(CohesionLayout::class)
      ->disableOriginalConstructor()
      ->onlyMethods([
        'id',
        'language',
        'get',
        'process',
        'getEntityType',
      ])
      ->getMock();

    $layout->callApi = FALSE;

    $layout->expects($this->any())
      ->method('id')
      ->willReturn(NULL);

    $layout->expects($this->any())
      ->method('getEntityType')
      ->willReturn($entityType);

    $language = new Language(['id' => 'en']);
    $layout->expects($this->any())
      ->method('language')
      ->willReturn($language);

    $stylesField = $this->prophesize(FieldItemListInterface::class);
    $stylesField->getValue()->willReturn([]);

    $templateField = $this->prophesize(FieldItemListInterface::class);
    $templateField->getValue()->willReturn([]);

    $layout->expects($this->any())
      ->method('get')
      ->willReturnCallback(function ($field) use ($stylesField, $templateField) {
        if ($field === 'styles') {
          return $stylesField->reveal();
        }
        if ($field === 'template') {
          return $templateField->reveal();
        }
        return NULL;
      });

    $layout->expects($this->never())
      ->method('process');

    $layout->preSave($storage->reveal());
  }

}
