<?php

namespace Drupal\cohesion_contenthub\EventSubscriber\EnqueueEligibility;

use Drupal\acquia_contenthub_publisher\ContentHubPublisherEvents;
use Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to entity eligibility to prevent enqueueing Cohesion Layout entities.
 */
class IsNotCohesionLayout implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ContentHubPublisherEvents::ENQUEUE_CANDIDATE_ENTITY][] =
      ['onEnqueueCandidateEntity', 60];
    return $events;
  }

  /**
   * Prevent Cohesion Layout entities from queueing.
   *
   * @param \Drupal\acquia_contenthub_publisher\Event\ContentHubEntityEligibilityEvent $event
   *   The event to determine entity eligibility.
   *
   * @throws \Exception
   */
  public function onEnqueueCandidateEntity(ContentHubEntityEligibilityEvent $event) {
    // Never export Cohesion Layout entities as main entities.
    // They should only be exported as dependencies.
    $entity = $event->getEntity();
    if ($entity instanceof CohesionLayout) {
      $event->setEligibility(FALSE);
      $event->stopPropagation();
    }
  }

}
