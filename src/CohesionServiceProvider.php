<?php

namespace Drupal\cohesion;

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
      $container->register('media_library.opener.cohesion', MediaLibraryCohesionOpener::class)
        ->setArguments(
          [
            new Reference('entity_type.manager'),
          ]
        )
        ->addTag('media_library.opener');
    }
  }

}
