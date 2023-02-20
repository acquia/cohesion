<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Component\Uuid\Uuid;

/**
 * Update entity references from IDs to UUIDs in arrays.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0033",
 * )
 */
class _0033EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function runUpdate(&$entity) {
    if ($entity instanceof EntityJsonValuesInterface && $entity->isLayoutCanvas()) {
      $json_values = $entity->getDecodedJsonValues(TRUE);
      if (property_exists($json_values, 'model')) {
        $this->updateEntityReferences($json_values->model);
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   * Recursively searches for entity references and updates IDs to UUIDs.
   */
  protected function updateEntityReferences(&$data) {

    foreach($data as &$value) {
      if ($this->isEntityReference($value)) {
        if ($uuid = $this->getEntityUUID($value->entity_type, $value->entity)) {
          $value->entity = $uuid;
        }
      }
      elseif ($this->isEntityBrowser($value)) {
        if ($uuid = $this->getEntityUUID($value->entityType, $value->entityId)) {
          $value->entityId = $uuid;
        }
      }
      elseif (is_object($value) || is_array($value)) {
        $this->updateEntityReferences($value);
      }
    }
  }

  /**
   * @param $entity_type
   * @param $entityId
   *
   * Get the uuid of an entity
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getEntityUUID($entity_type, $entityId) {
    $entity = \Drupal::entityTypeManager()
      ->getStorage($entity_type)
      ->load($entityId);
    if ($entity) {
      return $entity->uuid();
    }

    return FALSE;
  }

  /**
   * Checks if the object is an entity reference.
   * @param $value
   *
   * @return bool
   */
  protected function isEntityReference($value): bool {
    if (is_object($value)
      && property_exists($value, 'entity_type')
      && property_exists($value, 'entity')
      && is_string($value->entity)
      && Uuid::isValid($value->entity) === FALSE
    ) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Checks if the object is an entity browser.
   * @param $value
   *
   * @return bool
   */
  protected function isEntityBrowser($value): bool {
    if (is_object($value)
      && property_exists($value, 'entityType')
      && property_exists($value, 'entityId')
      && Uuid::isValid($value->entityId) === FALSE
    ) {
      return TRUE;
    }

    return FALSE;
  }

}
