<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "entity-reference" type.
 */
class EntityReferenceHandler extends EntityReferenceBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-entity-reference';
  const MAP = '/maps/field_level/entity-reference.map.yml';
  const SCHEMA = '/maps/field_level/entity-reference.schema.json';

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
