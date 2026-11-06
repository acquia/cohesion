<?php

namespace Drupal\Tests\cohesion\Unit\Theme;

use Drupal\cohesion\Theme\CohesionMediaLibraryThemeNegotiator;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Unit tests for CohesionMediaLibraryThemeNegotiator.
 *
 * @coversDefaultClass \Drupal\cohesion\Theme\CohesionMediaLibraryThemeNegotiator
 * @group Cohesion
 */
class CohesionMediaLibraryThemeNegotiatorTest extends UnitTestCase {

  /**
   * Creates a negotiator with mocked dependencies.
   *
   * @param string|null $adminTheme
   *   The admin theme to return from the mocked config.
   * @param string|null $queryOpenerId
   *   The media_library_opener_id to return from the request query string.
   *
   * @return \Drupal\cohesion\Theme\CohesionMediaLibraryThemeNegotiator
   *   The configured negotiator instance.
   */
  private function createNegotiator(?string $adminTheme = NULL, ?string $queryOpenerId = NULL): CohesionMediaLibraryThemeNegotiator {
    $config = $this->createMock(ImmutableConfig::class);
    $config->method('get')->with('admin')->willReturn($adminTheme);

    $configFactory = $this->createMock(ConfigFactoryInterface::class);
    $configFactory->method('get')->with('system.theme')->willReturn($config);

    $request = Request::create('/media-library', 'GET', $queryOpenerId !== NULL ? ['media_library_opener_id' => $queryOpenerId] : []);

    $requestStack = $this->createMock(RequestStack::class);
    $requestStack->method('getCurrentRequest')->willReturn($request);

    return new CohesionMediaLibraryThemeNegotiator($configFactory, $requestStack);
  }

  /**
   * @param string $routeName
   *   The route name to return from the mocked route match.
   * @param string|null $routeOpenerId
   *   The media_library_opener_id route parameter.
   * @param string|null $queryOpenerId
   *   The media_library_opener_id query parameter fallback.
   * @param bool $expected
   *   The expected result of applies().
   *
   * @dataProvider appliesProvider
   * @covers ::applies
   */
  public function testApplies(string $routeName, ?string $routeOpenerId, ?string $queryOpenerId, bool $expected): void {
    $routeMatch = $this->createMock(RouteMatchInterface::class);
    $routeMatch->method('getRouteName')->willReturn($routeName);
    $routeMatch->method('getParameter')->with('media_library_opener_id')->willReturn($routeOpenerId);

    $negotiator = $this->createNegotiator(NULL, $queryOpenerId);
    $this->assertSame($expected, $negotiator->applies($routeMatch));
  }

  /**
   * Data provider for ::testApplies.
   */
  public static function appliesProvider(): array {
    return [
      'wrong route' => ['some.other.route', 'media_library.opener.cohesion', NULL, FALSE],
      'right route, no opener id' => ['media_library.ui', NULL, NULL, FALSE],
      'right route, wrong opener id' => ['media_library.ui', 'media_library.opener.editor', NULL, FALSE],
      'right route, matching opener id from route parameter' => ['media_library.ui', 'media_library.opener.cohesion', NULL, TRUE],
      'right route, matching opener id from query fallback' => ['media_library.ui', NULL, 'media_library.opener.cohesion', TRUE],
      'right route, mismatched query fallback' => ['media_library.ui', NULL, 'media_library.opener.editor', FALSE],
    ];
  }

  /**
   * @covers ::determineActiveTheme
   */
  public function testDetermineActiveThemeReturnsConfiguredAdminTheme(): void {
    $negotiator = $this->createNegotiator('claro');
    $routeMatch = $this->createMock(RouteMatchInterface::class);
    $this->assertSame('claro', $negotiator->determineActiveTheme($routeMatch));
  }

  /**
   * @covers ::determineActiveTheme
   */
  public function testDetermineActiveThemeReturnsNullWhenUnconfigured(): void {
    $negotiator = $this->createNegotiator(NULL);
    $routeMatch = $this->createMock(RouteMatchInterface::class);
    $this->assertNull($negotiator->determineActiveTheme($routeMatch));
  }

}
