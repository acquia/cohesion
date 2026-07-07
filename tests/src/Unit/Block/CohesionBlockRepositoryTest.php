<?php

namespace Drupal\Tests\cohesion\Unit\Block;

use Drupal\block\BlockRepositoryInterface;
use Drupal\cohesion\Block\CohesionBlockRepository;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for CohesionBlockRepository.
 *
 * @coversDefaultClass \Drupal\cohesion\Block\CohesionBlockRepository
 *
 * @group Cohesion
 */
class CohesionBlockRepositoryTest extends UnitTestCase {

  /**
   * Creates a repository with mocked dependencies.
   *
   * @param array $regions
   *   Theme regions to return from mocked active theme.
   * @param callable $innerCb
   *   Callback for getVisibleBlocksPerRegion.
   *
   * @return \Drupal\cohesion\Block\CohesionBlockRepository
   *   The configured repository instance.
   */
  private function createRepository(array $regions, callable $innerCb): CohesionBlockRepository {
    $inner = $this->createMock(BlockRepositoryInterface::class);
    $inner->method('getVisibleBlocksPerRegion')->willReturnCallback($innerCb);

    $theme = $this->createMock(ActiveTheme::class);
    $theme->method('getRegions')->willReturn($regions);

    $themeManager = $this->createMock(ThemeManagerInterface::class);
    $themeManager->method('getActiveTheme')->willReturn($theme);

    return new CohesionBlockRepository($inner, $themeManager);
  }

  /**
   * Tests getVisibleBlocksPerRegion clears or preserves dx8_hidden metadata.
   *
   * @dataProvider getVisibleBlocksPerRegionProvider
   * @covers ::getVisibleBlocksPerRegion
   */
  public function testGetVisibleBlocksPerRegion(
    array $regions,
    callable $innerSetup,
    string $expectHidden,
    ?array $expectContentTags,
  ): void {
    $metadata = [];
    $innerCb = function (array &$m) use ($innerSetup) {
      $innerSetup($m);
      return array_fill_keys(array_keys($m), []);
    };
    $repository = $this->createRepository($regions, $innerCb);
    $repository->getVisibleBlocksPerRegion($metadata);

    $hidden = CohesionBlockRepository::HIDDEN_REGION;
    if ($expectHidden === 'cleared') {
      $this->assertArrayHasKey($hidden, $metadata);
      $this->assertEmpty($metadata[$hidden]->getCacheTags());
      $this->assertEmpty($metadata[$hidden]->getCacheContexts());
    }
    elseif ($expectHidden === 'passthrough') {
      $this->assertEquals(['block:1', 'node:123'], $metadata[$hidden]->getCacheTags());
    }
    elseif ($expectHidden === 'absent') {
      $this->assertArrayNotHasKey($hidden, $metadata);
    }

    if ($expectContentTags !== NULL) {
      $this->assertArrayHasKey('content', $metadata);
      $this->assertEquals($expectContentTags, $metadata['content']->getCacheTags());
    }
  }

  /**
   * Data provider for ::testGetVisibleBlocksPerRegion.
   */
  public static function getVisibleBlocksPerRegionProvider(): array {
    $hidden = CohesionBlockRepository::HIDDEN_REGION;
    return [
      'clears when theme has region' => [
        ['content', $hidden],
        function (array &$m) use ($hidden) {
          $m[$hidden] = (new CacheableMetadata())->setCacheTags(['block:1', 'node:123']);
          $m['content'] = (new CacheableMetadata())->setCacheTags(['block:2']);
        },
        'cleared',
        ['block:2'],
      ],
      'passthrough when theme lacks region' => [
        ['content', 'sidebar'],
        function (array &$m) use ($hidden) {
          $m[$hidden] = (new CacheableMetadata())->setCacheTags(['block:1', 'node:123']);
        },
        'passthrough',
        NULL,
      ],
      'unchanged when metadata lacks region' => [
        ['content', $hidden],
        function (array &$m) {
          $m['content'] = (new CacheableMetadata())->setCacheTags(['block:2']);
        },
        'absent',
        ['block:2'],
      ],
    ];
  }

  /**
   * @covers ::getUniqueMachineName
   */
  public function testGetUniqueMachineNameDelegatesToInner(): void {
    $inner = $this->createMock(BlockRepositoryInterface::class);
    $inner->expects($this->once())->method('getUniqueMachineName')->with('my_block', 'cohesion_theme')
      ->willReturn('cohesion_theme_my_block');

    $repo = new CohesionBlockRepository($inner, $this->createMock(ThemeManagerInterface::class));
    $this->assertSame('cohesion_theme_my_block', $repo->getUniqueMachineName('my_block', 'cohesion_theme'));
  }

}
