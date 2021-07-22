<?php

namespace Drupal\cohesion_contenthub\EventSubscriber\SerializeContentField;

use Drupal\acquia_contenthub\AcquiaContentHubEvents;
use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to entity field serialization to handle entity references.
 */
class CohesionLayoutCanvasSerializer implements EventSubscriberInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * CohesionLayoutCanvasSerializer constructor.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[AcquiaContentHubEvents::SERIALIZE_CONTENT_ENTITY_FIELD][] = ['onSerializeCohesionLayoutCdf', 150];
    return $events;
  }

  /**
   * Creates a new CDF representation of Content Entities.
   *
   * @param \Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent $event
   *   Event.
   *
   * @throws \Exception
   */
  public function onSerializeCohesionLayoutCdf(SerializeCdfEntityFieldEvent $event) {

    if ($event->getField()->getFieldDefinition()->getType() !== 'cohesion_entity_reference_revisions') {
      return;
    }
    $entity = $event->getEntity();
    if (!$event->getField()->entity instanceof CohesionLayout) {
      return;
    }
    $layoutCanvas = $event->getField()->entity->getLayoutCanvasInstance();
    $linkReferences = $layoutCanvas->getLinksReferences();
    $processedLinks = [];

    foreach ($linkReferences as $key => $value) {
      $linkedEntity = $this->entityTypeManager->getStorage($value['entity_type'])->load($value['entity_id']);

      if ($linkedEntity instanceof ContentEntityInterface && !isset($processedLinks[implode(":", $value)])) {
        $processedLinks[implode(":", $value)] = [
          'entity_type' => $value['entity_type'],
          'entity_id' => $value['entity_id'],
          'entity_uuid' => $linkedEntity->uuid(),
        ];
      }
    }

    $cdf = $event->getCdf($entity->uuid());
    $metadata = $cdf->getMetadata();
    $metadata['link_references'] = $processedLinks;
    $cdf->setMetadata($metadata);
  }

}
