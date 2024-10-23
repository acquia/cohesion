<?php

namespace Drupal\Tests\cohesion\Unit\Event\PostRebuildEventUnitTest;

use Drupal\cohesion\Event\PostRebuildEvent;
use Drupal\cohesion\Event\SiteStudioEvents;
use Drupal\Tests\Component\EventDispatcher\TestEventSubscriber;
use Drupal\Tests\UnitTestCase;
use Symfony\Contracts\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Class PostRebuildEventSubscriberMock.
 *
 * @package Drupal\Tests\cohesion\Unit\Event
 */
class PostRebuildEventSubscriberMock extends TestEventSubscriber {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      SiteStudioEvents::POST_REBUILD => 'postRebuild'
    ];
  }

  public function postRebuild(PostRebuildEvent $event) {

    if ($event->rebuildSuccess() === TRUE) {
      return 'After Site Studio rebuild was successful';
    } else {
      return 'After Site Studio rebuild was non-successful';
    }
  }

}

/**
 * Test for post-rebuild event.
 *
 * @group Cohesion
 *
 * @package Drupal\Tests\cohesion\Unit\Event\PostRebuildEventUnitTest
 *
 * @covers \Drupal\cohesion\Event\PostRebuildEvent
 */
class PostRebuildEventTest extends UnitTestCase {

  const PRE_REBUILD = SiteStudioEvents::POST_REBUILD;

  /**
   * @var \Symfony\Component\EventDispatcher\EventDispatcher
   */
  protected $dispatcher;

  /**
   * @var \Drupal\cohesion\Event\PostRebuildEvent
   */
  protected $successfulEvent;

  /**
   * @var
   */
  protected $successfulReturn;

  /**
   * @var \Drupal\cohesion\Event\PostRebuildEvent
   */
  protected $failedEvent;

  /**
   * @var
   */
  protected $failedReturn;

  public function setUp(): void {
    $this->dispatcher = new EventDispatcher();

    // Setup subscriber
    $eventSubscriber = new PostRebuildEventSubscriberMock();
    $this->dispatcher->addSubscriber($eventSubscriber);

    // Setup event which is successful.
    $successfulResults = [];
    $successfulSuccess = TRUE;
    $this->successfulEvent = new PostRebuildEvent($successfulResults, $successfulSuccess);
    $this->successfulReturn = $this->dispatcher->dispatch($this->successfulEvent, self::PRE_REBUILD);

    // Setup event which is not successful.
    $failedResults['error'] = 'some error';
    $failedSuccess = TRUE;
    $this->failedEvent = new PostRebuildEvent($failedResults, $failedSuccess);
    $this->failedReturn = $this->dispatcher->dispatch($this->failedEvent, self::PRE_REBUILD);
  }

  /**
   * @covers \Drupal\cohesion\Event\PostRebuildEvent
   */
  public function testEvent() {
    $this->assertTrue($this->dispatcher->hasListeners(self::PRE_REBUILD));
    $this->assertInstanceOf(Event::class, $this->dispatcher->dispatch($this->successfulEvent, self::PRE_REBUILD));
    $this->assertSame($this->successfulEvent, $this->successfulReturn);

    $this->assertInstanceOf(Event::class, $this->dispatcher->dispatch($this->failedEvent, self::PRE_REBUILD));
    $this->assertSame($this->failedEvent, $this->failedReturn);
  }

}
