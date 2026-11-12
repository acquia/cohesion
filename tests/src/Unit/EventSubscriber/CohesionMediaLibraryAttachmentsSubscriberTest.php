<?php

namespace Drupal\Tests\cohesion\Unit\EventSubscriber;

use Drupal\cohesion\EventSubscriber\CohesionMediaLibraryAttachmentsSubscriber;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ActiveTheme;
use Drupal\Core\Theme\ThemeManagerInterface;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * Unit tests for CohesionMediaLibraryAttachmentsSubscriber.
 *
 * @coversDefaultClass \Drupal\cohesion\EventSubscriber\CohesionMediaLibraryAttachmentsSubscriber
 * @group Cohesion
 */
class CohesionMediaLibraryAttachmentsSubscriberTest extends UnitTestCase {

  private function createEvent($controllerResult): ViewEvent {
    $kernel = $this->createMock(HttpKernelInterface::class);
    return new ViewEvent($kernel, Request::create('/media-library'), HttpKernelInterface::MAIN_REQUEST, $controllerResult);
  }

  /**
   * @covers ::onView
   */
  public function testAttachesCuratedVariablesLibraryWhenNegotiatorApplies(): void {
    $routeMatch = $this->createMock(RouteMatchInterface::class);

    $negotiator = $this->createMock(ThemeNegotiatorInterface::class);
    $negotiator->method('applies')->with($routeMatch)->willReturn(TRUE);

    $activeTheme = new ActiveTheme(['name' => 'default_admin']);
    $themeManager = $this->createMock(ThemeManagerInterface::class);
    $themeManager->method('getActiveTheme')->willReturn($activeTheme);

    $subscriber = new CohesionMediaLibraryAttachmentsSubscriber($negotiator, $themeManager, $routeMatch);

    $event = $this->createEvent(['#markup' => 'test']);
    $subscriber->onView($event);

    $result = $event->getControllerResult();
    $this->assertSame([
      'cohesion/media_library_theme_variables__default_admin',
      'cohesion/media_library_theme_components__default_admin',
      'default_admin/admin.drupal.dialog',
      'default_admin/views'
    ], $result['#attached']['library']);
  }

  /**
   * @covers ::onView
   */
  public function testDoesNothingForThemeWithoutACuratedFileList(): void {
    $routeMatch = $this->createMock(RouteMatchInterface::class);

    $negotiator = $this->createMock(ThemeNegotiatorInterface::class);
    $negotiator->method('applies')->with($routeMatch)->willReturn(TRUE);

    $activeTheme = new ActiveTheme(['name' => 'some_other_admin_theme']);
    $themeManager = $this->createMock(ThemeManagerInterface::class);
    $themeManager->method('getActiveTheme')->willReturn($activeTheme);

    $subscriber = new CohesionMediaLibraryAttachmentsSubscriber($negotiator, $themeManager, $routeMatch);

    $original = ['#markup' => 'test'];
    $event = $this->createEvent($original);
    $subscriber->onView($event);

    $this->assertSame($original, $event->getControllerResult());
  }

  /**
   * @covers ::onView
   */
  public function testDoesNothingWhenNegotiatorDoesNotApply(): void {
    $routeMatch = $this->createMock(RouteMatchInterface::class);

    $negotiator = $this->createMock(ThemeNegotiatorInterface::class);
    $negotiator->method('applies')->with($routeMatch)->willReturn(FALSE);

    $themeManager = $this->createMock(ThemeManagerInterface::class);
    $themeManager->expects($this->never())->method('getActiveTheme');

    $subscriber = new CohesionMediaLibraryAttachmentsSubscriber($negotiator, $themeManager, $routeMatch);

    $original = ['#markup' => 'test'];
    $event = $this->createEvent($original);
    $subscriber->onView($event);

    $this->assertSame($original, $event->getControllerResult());
  }

  /**
   * @covers ::onView
   */
  public function testDoesNothingWhenControllerResultIsNotARenderArray(): void {
    $routeMatch = $this->createMock(RouteMatchInterface::class);

    $negotiator = $this->createMock(ThemeNegotiatorInterface::class);
    $negotiator->expects($this->never())->method('applies');

    $themeManager = $this->createMock(ThemeManagerInterface::class);
    $themeManager->expects($this->never())->method('getActiveTheme');

    $subscriber = new CohesionMediaLibraryAttachmentsSubscriber($negotiator, $themeManager, $routeMatch);

    $event = $this->createEvent('not a render array');
    $subscriber->onView($event);

    $this->assertSame('not a render array', $event->getControllerResult());
  }

}
