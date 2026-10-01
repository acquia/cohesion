<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

/**
 * Handles Site Studio element of "entity reference" type.
 */
class EntityReferenceHandler extends EntityReferenceHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'entity-reference';
  const MAP = '/maps/element/entity-reference.map.yml';
  const SCHEMA = '/maps/element/entity-reference.schema.json';

  /**
   * {@inheritdoc}
   */
  protected function getEntityUuid(array $data): string {
    return $data['data']['value']->entity;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityType(array $data): string {
    return $data['data']['value']->entity_type;
  }

  /**
   * {@inheritdoc}
   */
  protected function hasEntityReference(array $data): bool {
    if (isset($data['data']['value']->entity_type, $data['data']['value']->entity)) {
      return TRUE;
    }
    return FALSE;
  }

}
