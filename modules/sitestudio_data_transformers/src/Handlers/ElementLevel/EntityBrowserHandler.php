<?php

namespace Drupal\sitestudio_data_transformers\Handlers\ElementLevel;

use Drupal\Core\Entity\EntityInterface;

/**
 * Handles Site Studio element of "entity browser" type.
 */
class EntityBrowserHandler extends EntityReferenceHandlerBase implements ElementLevelHandlerInterface {
  const ID = 'entity-browser';
  const MAP = '/maps/element/entity-browser.map.yml';
  const SCHEMA = '/maps/element/entity-browser.schema.json';

  /**
   * {@inheritdoc}
   */
  protected function getEntityUuid(array $data): string {
    return $data['data']['value']['entity']->entityUUID;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityType(array $data): string {
    return $data['data']['value']['entity']->entityType;
  }

  /**
   * {@inheritdoc}
   */
  protected function hasEntityReference(array $data): bool {
    if (isset($data['data']['value']['entity']->entityType, $data['data']['value']['entity']->entityUUID)) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * @param array $data
   * @return void
   */
  protected function addJsonApiLink(array &$data): void {
    if ($this->hasEntityReference($data)) {
      $entityType = $this->getEntityType($data);
      $entityUuid = $this->getEntityUuid($data);
      $results = $this->entityTypeManager->getStorage($entityType)->loadByProperties(['uuid' => $entityUuid]);
      $entity = reset($results);

      if ($entity instanceof EntityInterface) {
        $resourceType = $this->resourceTypeRepository->get($entityType, $entity->bundle());
        $routeName = sprintf('jsonapi.%s.individual', $resourceType->getTypeName());
        $data['data']['value']['jsonapi_link'] = $this->urlGenerator->generateFromRoute(
          $routeName,
          ['entity' => $entityUuid],
          ['absolute' => TRUE]
        );
      }
    }
  }

}
