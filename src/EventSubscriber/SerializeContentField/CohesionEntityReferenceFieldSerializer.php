<?php

namespace Drupal\cohesion\EventSubscriber\SerializeContentField;

use Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent;
use Drupal\acquia_contenthub\EventSubscriber\SerializeContentField\EntityReferenceFieldSerializer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Subscribes to entity field serialization to handle entity references.
 */
class CohesionEntityReferenceFieldSerializer extends EntityReferenceFieldSerializer implements EventSubscriberInterface {

  /**
   * Extract entity uuids as field values.
   *
   * @param \Drupal\acquia_contenthub\Event\SerializeCdfEntityFieldEvent $event
   *   The content entity field serialization event.
   *
   * @throws \Exception
   */
  public function onSerializeContentField(SerializeCdfEntityFieldEvent $event) {
    if ($event->getField()->getFieldDefinition()->getType() == 'cohesion_entity_reference_revisions') {
      $this->fieldTypes[] = 'cohesion_entity_reference_revisions';
      return parent::onSerializeContentField($event);
    }
  }

}
