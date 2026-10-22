<?php

namespace Drupal\sitestudio_data_transformers\Handlers\FormFieldLevel;

/**
 * Handles Site Studio form fields of "entity browser" type.
 */
class EntityBrowserHandler extends EntityReferenceBase implements FormFieldLevelHandlerInterface {

  const ID = 'form-entity-browser';
  const MAP = '/maps/field/entity-browser.map.yml';
  const SCHEMA = '/maps/field/entity-browser.schema.json';

  /**
   * {@inheritdoc}
   */
  protected function getEntityUuid(array $data): string {
    return $data['data']['value']->entity->entityUUID;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityType(array $data): string {
    return $data['data']['value']->entity->entityType;
  }

  /**
   * {@inheritdoc}
   */
  protected function hasEntityReference(array $data): bool {
    if (isset($data['data']['value']->entity->entityType, $data['data']['value']->entity->entityUUID)) {
      return TRUE;
    }
    return FALSE;
  }

}
