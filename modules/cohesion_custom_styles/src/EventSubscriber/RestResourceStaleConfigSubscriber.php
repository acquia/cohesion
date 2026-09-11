<?php

namespace Drupal\cohesion_custom_styles\EventSubscriber;

use Drupal\Core\Config\ConfigEvents;
use Drupal\Core\Config\StorageTransformEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Strips stale custom styles rest.resource.* config from imports.
 *
 * The plugins backing these resources were removed in 8.2.7. If a site's
 * config/sync export still contains the pre-removal rest.resource.* files,
 * a plain `drush config:import` would otherwise resurrect them and fatal
 * on the next route rebuild with "plugin does not exist". Stripping them
 * from the transformed sync storage means they never appear as a pending
 * change, and if they're already active on the site they get scheduled
 * for deletion instead.
 */
class RestResourceStaleConfigSubscriber implements EventSubscriberInterface {

  const RESOURCES_TO_REMOVE = [
    'rest.resource.cohesion_custom_styles',
    'rest.resource.cohesion_custom_style_type',
    'rest.resource.dx8_resource',
  ];

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[ConfigEvents::STORAGE_TRANSFORM_IMPORT][] = ['onStorageTransformImport'];
    return $events;
  }

  /**
   * Removes stale rest.resource.* entries from the import storage.
   *
   * @param \Drupal\Core\Config\StorageTransformEvent $event
   *   The storage transform event.
   */
  public function onStorageTransformImport(StorageTransformEvent $event) {
    $storage = $event->getStorage();
    foreach (self::RESOURCES_TO_REMOVE as $name) {
      if ($storage->exists($name)) {
        $storage->delete($name);
      }
    }
  }

}
