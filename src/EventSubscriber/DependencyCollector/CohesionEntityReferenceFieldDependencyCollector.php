<?php

namespace Drupal\cohesion\EventSubscriber\DependencyCollector;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\depcalc\EventSubscriber\DependencyCollector\EntityReferenceFieldDependencyCollector;

/**
 * Subscribes to dependency collection to extract referenced entities.
 */
class CohesionEntityReferenceFieldDependencyCollector extends EntityReferenceFieldDependencyCollector {

  /**
   * Determines if the field is of one of the specified types.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity.
   * @param string $field_name
   *   The field name.
   * @param \Drupal\Core\Field\FieldItemListInterface $field
   *   The field.
   *
   * @return bool
   *   Whether the field type is one of the specified ones.
   */
  public function fieldCondition(ContentEntityInterface $entity, $field_name, FieldItemListInterface $field) {
    return $field->getFieldDefinition()->getType() == 'cohesion_entity_reference_revisions';
  }

}
