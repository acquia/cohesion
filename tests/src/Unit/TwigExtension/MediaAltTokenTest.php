<?php

namespace Drupal\Tests\cohesion\Unit\TwigExtension;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion_templates\TwigExtension\TwigExtension;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\media\MediaInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Tests for TwigExtension media alt text token functionality.
 *
 * @group Cohesion
 *
 * @coversDefaultClass \Drupal\cohesion_templates\TwigExtension\TwigExtension
 */
class MediaAltTokenTest extends UnitTestCase {

  /**
   * The twig extension class under test.
   *
   * @var \Drupal\cohesion_templates\TwigExtension\TwigExtension
   */
  protected $twigExtension;

  /**
   * Mock entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface|\PHPUnit\Framework\MockObject\MockObject
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $renderer = $this->createMock('Drupal\Core\Render\RendererInterface');
    $token = $this->createMock('\Drupal\Core\Utility\Token');
    $themeRegistry = $this->createMock('\Drupal\Core\Theme\Registry');
    $twigEnvironment = $this->createMock('\Drupal\Core\Template\TwigEnvironment');
    $uuid = $this->createMock('\Drupal\Component\Uuid\UuidInterface');
    $this->entityTypeManager = $this->createMock('\Drupal\Core\Entity\EntityTypeManagerInterface');
    $stream_wrapper_manager = $this->createMock('\Drupal\Core\StreamWrapper\StreamWrapperManager');
    $extension_mime_type_guesser = $this->createMock('\Symfony\Component\Mime\MimeTypeGuesserInterface');
    $theme_manager = $this->createMock('\Drupal\Core\Theme\ThemeManagerInterface');
    $cohesion_utils = $this->createMock('\Drupal\cohesion\Services\CohesionUtils');
    $loggerChannelFactory = $this->createMock('\Drupal\Core\Logger\LoggerChannelFactoryInterface');
    $loggerChannelFactory->method('get')->willReturn($this->createMock('\Drupal\Core\Logger\LoggerChannelInterface'));
    $cohesionCurrentRouteMatch = $this->createMock('Drupal\cohesion\Routing\CohesionCurrentRouteMatch');
    $user = $this->createMock('Drupal\Core\Session\AccountInterface');
    $fileUrlGenerator = $this->createMock('\Drupal\Core\File\FileUrlGeneratorInterface');
    $entityRepository = $this->createMock('\Drupal\Core\Entity\EntityRepositoryInterface');
    $usageUpdateManager = $this->createMock('\Drupal\cohesion\UsageUpdateManager');

    $this->twigExtension = new TwigExtension(
      $renderer,
      $token,
      $themeRegistry,
      $twigEnvironment,
      $uuid,
      $this->entityTypeManager,
      $stream_wrapper_manager,
      $extension_mime_type_guesser,
      $theme_manager,
      $cohesion_utils,
      $loggerChannelFactory,
      $cohesionCurrentRouteMatch,
      $user,
      $fileUrlGenerator,
      $entityRepository,
      $usageUpdateManager
    );

    $languageManager = $this->createMock(LanguageManagerInterface::class);
    $languageManager->method('getCurrentLanguage')->willReturn(new Language(['id' => 'en']));

    $container = $this->createMock(ContainerInterface::class);
    $container->method('has')->willReturn(TRUE);
    $container->method('get')->willReturnCallback(function ($service) use ($languageManager) {
      if ($service === 'language_manager') {
        return $languageManager;
      }
      return NULL;
    });
    \Drupal::setContainer($container);
  }

  /**
   * Tests fetchMediaAlt returns alt text for standard media with image field.
   *
   * @covers ::fetchMediaAlt
   */
  public function testFetchMediaAltWithStandardMedia(): void {
    $mediaUuid = 'test-media-uuid-1234';
    $expectedAlt = 'A beautiful sunset over the ocean';

    $media = $this->createMediaMockWithImageField($expectedAlt);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->with(['uuid' => $mediaUuid])
      ->willReturn([$media]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $result = $this->invokeFetchMediaAlt("[media-reference:media:$mediaUuid]");

    $this->assertEquals($expectedAlt, $result);
  }

  /**
   * Tests fetchMediaAlt returns empty string when media not found.
   *
   * @covers ::tokenReplace
   */
  public function testFetchMediaAltWithNonExistentMedia(): void {
    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->willReturn([]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $result = $this->invokeFetchMediaAlt('[media-reference:media:nonexistent-uuid]');

    $this->assertEquals('', $result);
  }

  /**
   * Tests fetchMediaAlt returns empty string with invalid reference format.
   *
   * @covers ::tokenReplace
   */
  public function testFetchMediaAltWithInvalidFormat(): void {
    $result = $this->invokeFetchMediaAlt('[media-reference:invalid]');
    $this->assertEquals('', $result);

    $result = $this->invokeFetchMediaAlt('not-a-media-reference');
    $this->assertEquals('', $result);
  }

  /**
   * Tests fetchMediaAlt skips thumbnail field when looking for alt.
   *
   * @covers ::tokenReplace
   */
  public function testFetchMediaAltSkipsThumbnailField(): void {
    $mediaUuid = 'test-media-uuid-5678';
    $expectedAlt = 'Main image alt text';

    $media = $this->createMediaMockWithThumbnailAndImageField($expectedAlt);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->with(['uuid' => $mediaUuid])
      ->willReturn([$media]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $result = $this->invokeFetchMediaAlt("[media-reference:media:$mediaUuid]");

    $this->assertEquals($expectedAlt, $result);
  }

  /**
   * Tests fetchMediaAlt returns empty string when media has no alt field.
   *
   * @covers ::tokenReplace
   */
  public function testFetchMediaAltWithNoAltField(): void {
    $mediaUuid = 'test-media-uuid-no-alt';

    $fieldDefinition = $this->createMock(FieldDefinitionInterface::class);
    $fieldDefinition->method('getSettings')->willReturn(['alt_field' => FALSE]);

    $field = $this->createMock(FieldItemListInterface::class);
    $field->method('getFieldDefinition')->willReturn($fieldDefinition);

    $media = $this->createMock(MediaInterface::class);
    $media->method('getFields')->willReturn(['field_no_alt' => $field]);
    $media->method('hasField')->willReturn(FALSE);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->with(['uuid' => $mediaUuid])
      ->willReturn([$media]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $result = $this->invokeFetchMediaAlt("[media-reference:media:$mediaUuid]");

    $this->assertEquals('', $result);
  }

  /**
   * Tests buildRepeaterIndexPath collects indices from nested context.
   *
   * @covers ::tokenReplace
   * @dataProvider repeaterIndexPathProvider
   */
  public function testBuildRepeaterIndexPath(array $context, array $expectedIndices): void {
    $result = $this->invokeBuildRepeaterIndexPath($context);
    $this->assertEquals($expectedIndices, $result);
  }

  /**
   * Data provider for testBuildRepeaterIndexPath.
   */
  public static function repeaterIndexPathProvider(): array {
    return [
      'single level repeater' => [
        [
          '_key' => '#0',
          '_seq' => [['item1'], ['item2']],
        ],
        [0],
      ],
      'nested two level repeater' => [
        [
          '_key' => '#1',
          '_parent' => [
            '_key' => '#2',
          ],
        ],
        [2, 1],
      ],
      'deeply nested three level repeater' => [
        [
          '_key' => '#0',
          '_parent' => [
            '_key' => '#1',
            '_parent' => [
              '_key' => '#2',
            ],
          ],
        ],
        [2, 1, 0],
      ],
      'no repeater context' => [
        [],
        [],
      ],
    ];
  }

  /**
   * Tests searchForMediaReference finds media reference in nested structure.
   *
   * @covers ::tokenReplace
   */
  public function testSearchForMediaReferenceInNestedStructure(): void {
    $mediaUuid = 'nested-media-uuid';
    $expectedAlt = 'Nested media alt';

    $structure = [
      'level1' => [
        'level2' => [
          'mediaField' => "[media-reference:media:$mediaUuid]",
        ],
      ],
    ];

    $media = $this->createMediaMockWithImageField($expectedAlt);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->with(['uuid' => $mediaUuid])
      ->willReturn([$media]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $result = $this->invokeSearchForMediaReference($structure);

    $this->assertEquals($expectedAlt, $result);
  }

  /**
   * Tests searchForMediaReference returns null when no media reference found.
   *
   * @covers ::tokenReplace
   */
  public function testSearchForMediaReferenceReturnsNullWhenNotFound(): void {
    $structure = [
      'field1' => 'some text',
      'field2' => ['nested' => 'value'],
    ];

    $result = $this->invokeSearchForMediaReference($structure);

    $this->assertNull($result);
  }

  /**
   * Tests findMediaReferenceByIndexPath navigates to correct index.
   *
   * @covers ::tokenReplace
   */
  public function testFindMediaReferenceByIndexPath(): void {
    $mediaUuid = 'indexed-media-uuid';
    $expectedAlt = 'Indexed media alt';

    $structure = [
      [
        'mediaField' => '[media-reference:media:wrong-uuid]',
      ],
      [
        'mediaField' => "[media-reference:media:$mediaUuid]",
      ],
    ];

    $media = $this->createMediaMockWithImageField($expectedAlt);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->willReturnCallback(function ($props) use ($mediaUuid, $media) {
        if ($props['uuid'] === $mediaUuid) {
          return [$media];
        }
        return [];
      });

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $result = $this->invokeFindMediaReferenceByIndexPath($structure, [1]);

    $this->assertEquals($expectedAlt, $result);
  }

  /**
   * Creates a mock media entity with an image field containing alt text.
   *
   * @param string $altText
   *   The alt text to return.
   *
   * @return \Drupal\media\MediaInterface|\PHPUnit\Framework\MockObject\MockObject
   *   The mock media entity.
   */
  protected function createMediaMockWithImageField(string $altText): MediaInterface {
    $fieldDefinition = $this->createMock(FieldDefinitionInterface::class);
    $fieldDefinition->method('getSettings')->willReturn(['alt_field' => TRUE]);

    $imageField = $this->createMock(FieldItemListInterface::class);
    $imageField->method('getFieldDefinition')->willReturn($fieldDefinition);

    // Create an object that will be returned by get() and has the alt property.
    $fieldValue = new \stdClass();
    $fieldValue->alt = $altText;

    $media = $this->createMock(MediaInterface::class);
    $media->method('getFields')->willReturn(['field_media_image' => $imageField]);
    $media->method('hasField')->willReturn(FALSE);
    $media->method('get')->with('field_media_image')->willReturn($fieldValue);

    return $media;
  }

  /**
   * Creates a mock media with both thumbnail and a real image field.
   *
   * @param string $altText
   *   The alt text for the main image field.
   *
   * @return \Drupal\media\MediaInterface|\PHPUnit\Framework\MockObject\MockObject
   *   The mock media entity.
   */
  protected function createMediaMockWithThumbnailAndImageField(string $altText): MediaInterface {
    $thumbnailDefinition = $this->createMock(FieldDefinitionInterface::class);
    $thumbnailDefinition->method('getSettings')->willReturn(['alt_field' => TRUE]);

    $thumbnailField = $this->createMock(FieldItemListInterface::class);
    $thumbnailField->method('getFieldDefinition')->willReturn($thumbnailDefinition);

    $imageDefinition = $this->createMock(FieldDefinitionInterface::class);
    $imageDefinition->method('getSettings')->willReturn(['alt_field' => TRUE]);

    $imageField = $this->createMock(FieldItemListInterface::class);
    $imageField->method('getFieldDefinition')->willReturn($imageDefinition);

    // Create field value objects for get() returns.
    $thumbnailValue = new \stdClass();
    $thumbnailValue->alt = 'Thumbnail alt - should be skipped';

    $imageValue = new \stdClass();
    $imageValue->alt = $altText;

    $media = $this->createMock(MediaInterface::class);
    $media->method('getFields')->willReturn([
      'thumbnail' => $thumbnailField,
      'field_media_image' => $imageField,
    ]);
    $media->method('hasField')->willReturn(FALSE);
    $media->method('get')->willReturnCallback(function ($fieldName) use ($thumbnailValue, $imageValue) {
      return $fieldName === 'thumbnail' ? $thumbnailValue : $imageValue;
    });

    return $media;
  }

  /**
   * Invokes the private fetchMediaAlt method.
   *
   * @param string $elementModelValue
   *   The media reference string.
   *
   * @return string
   *   The alt text result.
   */
  protected function invokeFetchMediaAlt(string $elementModelValue): string {
    $reflection = new \ReflectionClass($this->twigExtension);
    $method = $reflection->getMethod('fetchMediaAlt');
    $method->setAccessible(TRUE);
    return $method->invoke($this->twigExtension, $elementModelValue);
  }

  /**
   * Invokes the private buildRepeaterIndexPath method.
   *
   * @param array $context
   *   The Twig context.
   *
   * @return array
   *   The indices array.
   */
  protected function invokeBuildRepeaterIndexPath(array $context): array {
    $reflection = new \ReflectionClass($this->twigExtension);
    $method = $reflection->getMethod('buildRepeaterIndexPath');
    $method->setAccessible(TRUE);
    return $method->invoke($this->twigExtension, $context);
  }

  /**
   * Invokes the private searchForMediaReference method.
   *
   * @param mixed $structure
   *   The structure to search.
   *
   * @return string|null
   *   The alt text or null.
   */
  protected function invokeSearchForMediaReference(mixed $structure): ?string {
    $reflection = new \ReflectionClass($this->twigExtension);
    $method = $reflection->getMethod('searchForMediaReference');
    $method->setAccessible(TRUE);
    return $method->invoke($this->twigExtension, $structure);
  }

  /**
   * Invokes the private findMediaReferenceByIndexPath method.
   *
   * @param mixed $structure
   *   The structure to search.
   * @param array $indices
   *   The index path.
   *
   * @return string|null
   *   The alt text or null.
   */
  protected function invokeFindMediaReferenceByIndexPath(mixed $structure, array $indices): ?string {
    $reflection = new \ReflectionClass($this->twigExtension);
    $method = $reflection->getMethod('findMediaReferenceByIndexPath');
    $method->setAccessible(TRUE);
    return $method->invoke($this->twigExtension, $structure, $indices);
  }

  /**
   * Tests tokenReplace returns alt text for media-entity-reference token.
   *
   * @covers ::tokenReplace
   */
  public function testTokenReplaceMediaEntityReferenceWithValidContext(): void {
    $mediaUuid = 'component-media-uuid';
    $componentUuid = 'test-component-uuid';
    $expectedAlt = 'Component media alt text';

    $media = $this->createMediaMockWithImageField($expectedAlt);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->with(['uuid' => $mediaUuid])
      ->willReturn([$media]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $model = $this->createMock('Drupal\cohesion\LayoutCanvas\ElementModel');
    $model->method('getValues')->willReturn([
      "[media-reference:media:$mediaUuid]",
    ]);

    $element = $this->createMock('Drupal\cohesion\LayoutCanvas\Element');
    $element->method('isComponent')->willReturn(TRUE);
    $element->method('getUUID')->willReturn($componentUuid);
    $element->method('getModel')->willReturn($model);

    $layoutCanvas = $this->createMock('Drupal\cohesion\LayoutCanvas\LayoutCanvas');
    $layoutCanvas->method('iterateCanvas')->willReturn(new \ArrayIterator([$element]));

    $entity = $this->createMock(EntityJsonValuesInterface::class);
    $entity->method('getLayoutCanvasInstance')->willReturn($layoutCanvas);

    $context = [
      'componentUuid' => $componentUuid,
      'layout_builder_entity' => ['entity' => $entity],
    ];

    $result = $this->twigExtension->tokenReplace(
      '[media-entity-reference:media-alt]',
      [],
      $context
    );

    $this->assertEquals($expectedAlt, $result);
  }

  /**
   * Tests tokenReplace returns null when context is missing componentUuid.
   *
   * @covers ::tokenReplace
   */
  public function testTokenReplaceMediaEntityReferenceMissingComponentUuid(): void {
    $context = [
      'layout_builder_entity' => ['entity' => $this->createMock(EntityJsonValuesInterface::class)],
    ];

    $result = $this->twigExtension->tokenReplace(
      '[media-entity-reference:media-alt]',
      [],
      $context
    );

    $this->assertNull($result);
  }

  /**
   * Tests tokenReplace returns null when context is missing entity.
   *
   * @covers ::tokenReplace
   */
  public function testTokenReplaceMediaEntityReferenceMissingEntity(): void {
    $context = [
      'componentUuid' => 'some-uuid',
    ];

    $result = $this->twigExtension->tokenReplace(
      '[media-entity-reference:media-alt]',
      [],
      $context
    );

    $this->assertNull($result);
  }

  /**
   * Tests tokenReplace handles repeater context correctly.
   *
   * @covers ::tokenReplace
   */
  public function testTokenReplaceMediaEntityReferenceWithRepeaterContext(): void {
    $mediaUuid = 'repeater-media-uuid';
    $componentUuid = 'repeater-component-uuid';
    $expectedAlt = 'Repeater media alt text';

    $media = $this->createMediaMockWithImageField($expectedAlt);

    $storage = $this->createMock(EntityStorageInterface::class);
    $storage->method('loadByProperties')
      ->with(['uuid' => $mediaUuid])
      ->willReturn([$media]);

    $this->entityTypeManager->method('getStorage')
      ->with('media')
      ->willReturn($storage);

    $model = $this->createMock('Drupal\cohesion\LayoutCanvas\ElementModel');
    $model->method('getValues')->willReturn([
      [
        ['mediaField' => '[media-reference:media:wrong-uuid]'],
        ['mediaField' => "[media-reference:media:$mediaUuid]"],
      ],
    ]);

    $element = $this->createMock('Drupal\cohesion\LayoutCanvas\Element');
    $element->method('isComponent')->willReturn(TRUE);
    $element->method('getUUID')->willReturn($componentUuid);
    $element->method('getModel')->willReturn($model);

    $layoutCanvas = $this->createMock('Drupal\cohesion\LayoutCanvas\LayoutCanvas');
    $layoutCanvas->method('iterateCanvas')->willReturn(new \ArrayIterator([$element]));

    $entity = $this->createMock(EntityJsonValuesInterface::class);
    $entity->method('getLayoutCanvasInstance')->willReturn($layoutCanvas);

    $context = [
      'componentUuid' => $componentUuid,
      'layout_builder_entity' => ['entity' => $entity],
      '_seq' => [['item1'], ['item2']],
      '_key' => '#1',
    ];

    $result = $this->twigExtension->tokenReplace(
      '[media-entity-reference:media-alt]',
      [],
      $context
    );

    $this->assertEquals($expectedAlt, $result);
  }

  /**
   * Tests tokenReplace returns null when component UUID doesn't match.
   *
   * @covers ::tokenReplace
   */
  public function testTokenReplaceMediaEntityReferenceComponentMismatch(): void {
    $model = $this->createMock('Drupal\cohesion\LayoutCanvas\ElementModel');
    $model->method('getValues')->willReturn(['[media-reference:media:some-uuid]']);

    $element = $this->createMock('Drupal\cohesion\LayoutCanvas\Element');
    $element->method('isComponent')->willReturn(TRUE);
    $element->method('getUUID')->willReturn('different-component-uuid');
    $element->method('getModel')->willReturn($model);

    $layoutCanvas = $this->createMock('Drupal\cohesion\LayoutCanvas\LayoutCanvas');
    $layoutCanvas->method('iterateCanvas')->willReturn(new \ArrayIterator([$element]));

    $entity = $this->createMock(EntityJsonValuesInterface::class);
    $entity->method('getLayoutCanvasInstance')->willReturn($layoutCanvas);

    $context = [
      'componentUuid' => 'expected-component-uuid',
      'layout_builder_entity' => ['entity' => $entity],
    ];

    $result = $this->twigExtension->tokenReplace(
      '[media-entity-reference:media-alt]',
      [],
      $context
    );

    $this->assertNull($result);
  }

  /**
   * Tests tokenReplace returns null when entity has no layout canvas.
   *
   * @covers ::tokenReplace
   */
  public function testTokenReplaceMediaEntityReferenceNoLayoutCanvas(): void {
    $entity = $this->createMock(EntityJsonValuesInterface::class);
    $entity->method('getLayoutCanvasInstance')->willReturn(NULL);

    $context = [
      'componentUuid' => 'some-uuid',
      'layout_builder_entity' => ['entity' => $entity],
    ];

    $result = $this->twigExtension->tokenReplace(
      '[media-entity-reference:media-alt]',
      [],
      $context
    );

    $this->assertNull($result);
  }

}