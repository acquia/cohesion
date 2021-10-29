<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\cohesion\Event\FrontendUrlsEvent;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 *  Visual page builder event subscriber for frontend app.
 *
 */
class CohesionFrontendUrlSubscriber implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      FrontendUrlsEvent::EVENT_NAME => 'registerUrls',
    ];
  }

  /**
   * URls for javascript app
   *
   * @param \Drupal\cohesion\Event\FrontendUrlsEvent $event
   */
  public function registerUrls(FrontendUrlsEvent $event) {
    // Component categories
    $url = Url::fromRoute('cohesion_elements.categories', ['element_type' => 'component'])->toString();
    $event->addFrontEndUrl('component-categories', [
      'url' => $url,
      'method' => 'GET'
    ]);

    $event->addAdminUrls('component-categories', [
      'url' => $url,
      'method' => 'GET'
    ]);

    // helper categories
    $url = Url::fromRoute('cohesion_elements.categories', ['element_type' => 'helper'])->toString();
    $event->addFrontEndUrl('helper-categories', [
      'url' => $url,
      'method' => 'GET'
    ]);

    $event->addAdminUrls('helper-categories', [
      'url' => $url,
      'method' => 'GET'
    ]);

    // Save element
    $url = Url::fromRoute('drupal_data_endpoint.element_save')->toString();
    $event->addFrontEndUrl('element-save', [
      'url' => $url,
      'method' => 'POST'
    ]);

    $event->addAdminUrls('element-save', [
      'url' => $url,
      'method' => 'POST'
    ]);
  }

}
