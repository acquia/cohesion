<?php

namespace Drupal\example_custom_select\EventSubscriber;

use Drupal\cohesion_elements\Event\CohesionLayoutViewBuilderEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Event Subscriber for CohesionLayoutViewBuilderEvent.
 */
final class ExampleCustomSelectSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      CohesionLayoutViewBuilderEvent::ALTER => 'alterView',
    ];
  }

  /**
   * @param \Drupal\cohesion_elements\Event\CohesionLayoutViewBuilderEvent $event
   * @return void
   */
  public function alterView(CohesionLayoutViewBuilderEvent $event) {

    $build = $event->getBuild();
    // Attach our custom JS library in a Page Builder context.
    $build['#attached']['library'][] = 'example_custom_select/admin-custom-select';

    $event->setBuild($build);
  }

}
