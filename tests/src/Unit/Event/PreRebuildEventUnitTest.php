<?php

namespace Drupal\Tests\cohesion\Unit\Event\PreRebuildEventUnitTest;

use Drupal\cohesion\Event\PreRebuildEvent;
use Drupal\cohesion\Event\SiteStudioEvents;
use Drupal\Tests\Component\EventDispatcher\TestEventSubscriber;
use Drupal\Tests\UnitTestCase;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PreRebuildEventSubscriberMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Event
 */
class PreRebuildEventSubscriberMock extends TestEventSubscriber {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SiteStudioEvents::PRE_REBUILD => 'preRebuild'
    ];
  }

  public function preRebuild(PreRebuildEvent $event) {
    return 'Before Site Studio rebuild';
  }

}

/**
 * Test for pre-rebuild event.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion\Unit\Event\PreRebuildEventUnitTest
 *
 * @covers \Drupal\cohesion\Event\PreRebuildEvent
 */
class PreRebuildEventTest extends UnitTestCase {

  const PRE_REBUILD = SiteStudioEvents::PRE_REBUILD;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $dispatcher;

  /**
   * @var \Drupal\cohesion\Event\PreRebuildEvent
   */
  protected $event;

  /**
   * @var
   */
  protected $return;

  public function setUp(): void {
    $this->dispatcher = new EventDispatcher();

    // Setup subscriber
    $eventSubscriber = new PreRebuildEventSubscriberMock();
    $this->dispatcher->addSubscriber($eventSubscriber);

    // Setup event
    $this->event = new PreRebuildEvent();
    $this->return = $this->dispatcher->dispatch($this->event, self::PRE_REBUILD);
  }

  /**
   * @covers \Drupal\cohesion\Event\PreRebuildEvent
   */
  public function testEvent() {
    $this->assertTrue($this->dispatcher->hasListeners(self::PRE_REBUILD));
    $this->assertInstanceOf(Event::class, $this->dispatcher->dispatch(new PreRebuildEvent(), self::PRE_REBUILD));
    $this->assertSame($this->event, $this->return);
  }

}
