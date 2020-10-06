<?php

namespace Drupal\cohesion;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Symfony\Component\DependencyInjection\Reference;
use Drupal\cohesion\Controller\CohesionMediaLibraryUiBuilder;
use Drupal\cohesion\EventSubscriber\DependencyCollector\CohesionEntityReferenceFieldDependencyCollector;
use Drupal\cohesion\EventSubscriber\SerializeContentField\CohesionEntityReferenceFieldSerializer;
use Drupal\cohesion\EventSubscriber\UnserializeContentField\CohesionEntityReferenceField;

/**
 * Site Studio service provider
 */
class CohesionServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    $modules = $container->getParameter('container.modules');

    if (isset($modules['media_library'])) {
      $container->register('media_library.opener.cohesion', MediaLibraryCohesionOpener::class)
        ->setArguments(
          [
            new Reference('entity_type.manager')
          ]
        );

      $container->register('cohesion.media_library.ui_builder', CohesionMediaLibraryUiBuilder::class)
        ->setArguments(
          [
            new Reference('entity_type.manager'),
            new Reference('request_stack'),
            new Reference('views.executable'),
            new Reference('form_builder'),
            new Reference('media_library.opener_resolver')
          ]
        );
    }

    if (isset($modules['acquia_contenthub'])) {
      $container->register('cohesion_entity_reference.field.cdf.unserializer', CohesionEntityReferenceField::class)
        ->addTag('event_subscriber');
      $container->register('cohesion_entity_reference.field.cdf.serializer', CohesionEntityReferenceFieldSerializer::class)
        ->addTag('event_subscriber');
      $container->register('cohesion_entity_reference.dependency_calculator', CohesionEntityReferenceFieldDependencyCollector::class)
        ->addTag('event_subscriber');
    }
  }

}
