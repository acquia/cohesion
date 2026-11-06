<?php

namespace Drupal\cohesion;

use Drupal\cohesion\EventSubscriber\CohesionMediaLibraryAttachmentsSubscriber;
use Drupal\cohesion\Theme\CohesionMediaLibraryThemeNegotiator;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Site Studio service provider.
 */
class CohesionServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $modules = $container->getParameter('container.modules');

    // The cohesion.template_storage alias points to a single template storage
    // backend. That backend needs to be designated as a Twig template loader,
    // since it doesn't appear to be possible to add tags to a service alias.
    $template_storage = (string) $container->getAlias('cohesion.template_storage');
    $container->getDefinition($template_storage)
      ->addTag('twig.loader', [
        'priority' => 200,
      ]);

    if (isset($modules['media_library'])) {
      $container->register(
        'media_library.opener.cohesion',
        MediaLibraryCohesionOpener::class
      )->setArguments(
          [
            new Reference('entity_type.manager'),
          ]
        )
        ->addTag('media_library.opener');

      // Priority 1001 is deliberately just above core's AjaxBasePageNegotiator,
      // BatchNegotiator and AdminDemoNegotiator (all priority 1000), so this
      // wins theme negotiation for the cohesion media library opener without
      // claiming an arbitrarily large priority.
      $container->register(
        'cohesion.theme_negotiator.media_library',
        CohesionMediaLibraryThemeNegotiator::class
      )->setArguments(
          [
            new Reference('config.factory'),
            new Reference('request_stack'),
          ]
        )
        ->addTag('theme_negotiator', ['priority' => 1001]);

      // The theme negotiator above makes the admin theme active for the
      // media library AJAX dialog, but Drupal only attaches a theme's own
      // top-level libraries (e.g. an admin theme's base/variables CSS) via
      // the full HTML page wrapper, not the AJAX wrapper. This subscriber
      // attaches a curated, dialog-safe subset of them to the dialog's
      // render array directly instead.
      // @see cohesion_library_info_build()
      $container->register(
        'cohesion.event_subscriber.media_library_attachments',
        CohesionMediaLibraryAttachmentsSubscriber::class
      )->setArguments(
          [
            new Reference('cohesion.theme_negotiator.media_library'),
            new Reference('theme.manager'),
            new Reference('current_route_match'),
          ]
        )
        ->addTag('event_subscriber');
    }
  }

}
