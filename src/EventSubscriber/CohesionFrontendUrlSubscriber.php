<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
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
      CohesionJsAppUrlsEvent::FRONTEND_URL => 'registerFrontendUrls',
      CohesionJsAppUrlsEvent::ADMIN_URL => 'registerAdminUrls',
    ];
  }

  /**
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerFrontendUrls(CohesionJsAppUrlsEvent $event) {
    $this->registerCommonUrls($event);
  }

  public function registerAdminUrls(CohesionJsAppUrlsEvent $event) {
    $this->registerCommonUrls($event);

    // URL for form field elements
    $url = Url::fromRoute('cohesion_website_settings.elements', ['group' => 'form_elements'])->toString();
    $event->addUrl('field-list', [
      'url' => $url,
      'method' => 'GET'
    ]);
  }

  /**
   * URL needed for the js app for both admin and visual page builder
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerCommonUrls(CohesionJsAppUrlsEvent $event) {
    // Component categories
    $url = Url::fromRoute('cohesion_elements.categories', ['element_type' => 'component'])->toString();
    $event->addUrl('component-categories', [
      'url' => $url,
      'method' => 'GET'
    ]);

    // helper categories
    $url = Url::fromRoute('cohesion_elements.categories', ['element_type' => 'helper'])->toString();
    $event->addUrl('helper-categories', [
      'url' => $url,
      'method' => 'GET'
    ]);

    // Save element
    $url = Url::fromRoute('drupal_data_endpoint.element_save')->toString();
    $event->addUrl('element-save', [
      'url' => $url,
      'method' => 'POST'
    ]);

    // Sidebar element
    $url = Url::fromRoute('sitestudio-page-builder.layout_canvas.frontend_edit_component', ['coh_clean_page' => 'true'])->toString();
    $event->addUrl('sidebar-edit', [
      'url' => $url,
      'method' => 'GET'
    ]);
  }

}
