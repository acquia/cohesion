<?php

namespace Drupal\cohesion\EventSubscriber;

use Drupal\cohesion\Event\CohesionJsAppUrlsEvent;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Visual page builder event subscriber for frontend app.
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

    // URL for form field elements.
    $url = Url::fromRoute('cohesion_website_settings.elements', ['group' => 'form_elements'])->toString();
    $event->addUrl('field-list', [
      'url' => $url,
      'method' => 'GET',
    ]);
  }

  /**
   * URL needed for the js app for both admin and visual page builder
   *
   * @param \Drupal\cohesion\Event\CohesionJsAppUrlsEvent $event
   */
  public function registerCommonUrls(CohesionJsAppUrlsEvent $event) {
    // Component categories.
    $url = Url::fromRoute('cohesion_elements.categories', ['element_type' => 'component'])->toString();
    $event->addUrl('component-categories', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Helper categories.
    $url = Url::fromRoute('cohesion_elements.categories', ['element_type' => 'helper'])->toString();
    $event->addUrl('helper-categories', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Save element.
    $url = Url::fromRoute('drupal_data_endpoint.element_save')->toString();
    $event->addUrl('element-save', [
      'url' => $url,
      'method' => 'POST',
    ]);

    // Sidebar element.
    $url = Url::fromRoute('sitestudio-page-builder.layout_canvas.frontend_edit_component', ['coh_clean_page' => 'true'])->toString();
    $event->addUrl('sidebar-edit', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Component content by ID.
    $url = Url::fromRoute('cohesion_elements.endpoints.component_contents_ids')->toString();
    $event->addUrl('component-content-by-id', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Components model.
    $url = Url::fromRoute('drupal_data_endpoint.component_models')->toString();
    $event->addUrl('cohesion-components', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Helper model.
    $url = Url::fromRoute('drupal_data_endpoint.helper_model')->toString();
    $event->addUrl('helper-updated', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Component add URL.
    $url = Url::fromUserInput(\Drupal::entityTypeManager()->getDefinition('cohesion_component')->getLinkTemplate('add-form'))->toString();
    $event->addUrl('component-add', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Helper add URL.
    $url = Url::fromUserInput(\Drupal::entityTypeManager()->getDefinition('cohesion_helper')->getLinkTemplate('add-form'))->toString();
    $event->addUrl('helper-add', [
      'url' => $url,
      'method' => 'GET',
    ]);

    // Component content add URL.
    $url = Url::fromUserInput(\Drupal::entityTypeManager()->getDefinition('component_content')->getLinkTemplate('collection'))->toString();
    $event->addUrl('component-content-add', [
      'url' => $url,
      'method' => 'GET',
    ]);
  }

}
