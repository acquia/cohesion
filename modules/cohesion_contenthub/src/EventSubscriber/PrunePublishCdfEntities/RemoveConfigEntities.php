<?php

namespace Drupal\cohesion_contenthub\EventSubscriber\PrunePublishCdfEntities;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\PrunePublishCdfEntitiesEvent;
use Drupal\Core\Language\LanguageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Removes config entities from export.
 *
 * @package Drupal\acquia_contenthub_publisher\EventSubscriber\PublishEntities
 */
class RemoveConfigEntities implements EventSubscriberInterface {

  const COHESION_CONFIG_ENTITY_TYPES = [
    "cohesion_base_styles",
    "custom_style_type",
    "cohesion_custom_style",
    "cohesion_component_category",
    "cohesion_component",
    "cohesion_helper_category",
    "cohesion_helper",
    "cohesion_style_helper",
    "cohesion_sync_package",
    "cohesion_content_templates",
    "cohesion_view_templates",
    "cohesion_master_templates",
    "cohesion_menu_templates",
    "cohesion_website_settings",
    "cohesion_icon_library",
    "cohesion_font_stack",
    "cohesion_scss_variable",
    "cohesion_color",
    "cohesion_font_library",
  ];

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcquiaContentHubEvents::PRUNE_PUBLISH_CDF_ENTITIES][] =
      ['onPrunePublishCdfEntities', 100];
    return $events;
  }

  /**
   * Removes config entities before publishing.
   *
   * @param \Drupal\acquia_contenthub\Event\PrunePublishCdfEntitiesEvent $event
   *   The Content Hub publish entities event.
   */
  public function onPrunePublishCdfEntities(PrunePublishCdfEntitiesEvent $event) {
    $document = $event->getDocument();
    $entities = $document->getEntities();

    foreach ($entities as $uuid => $entity) {
      $entity_type = $entity->getAttribute('entity_type')->getValue()[LanguageInterface::LANGCODE_NOT_SPECIFIED];
      if (in_array($entity_type, self::COHESION_CONFIG_ENTITY_TYPES)) {
        $document->removeCdfEntity($uuid);
      }
    }
  }

}
