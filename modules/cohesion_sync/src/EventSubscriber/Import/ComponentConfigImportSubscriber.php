<?php

namespace Drupal\cohesion_sync\EventSubscriber\Import;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;

/**
 * Cohesion Config import subscriber for handling Component validation.
 *
 * @package Drupal\cohesion_sync\EventSubscriber
 */
class ComponentConfigImportSubscriber extends CohesionConfigImportSubscriberBase {

  const CONFIG_PREFIX = 'cohesion_elements.cohesion_component.';

  /**
   * {@inheritdocc}
   */
  protected function checkForBrokenEntities(
    array $in_use_list,
    array $source_config,
    array $target_config,
  ) : array {
    $broken_entities = [];

    foreach ($in_use_list as $uuid => $type) {
      $cohesionLayoutEntities = $this->getCohesionLayout($type, $uuid);
      foreach ($cohesionLayoutEntities as $cohesionLayoutEntity) {
        if ($this->hasDefinedContentForRemovedComponentField(
          $cohesionLayoutEntity,
          $source_config['json_values'],
          $target_config['id']
        )) {
          $affected_entity = $this->entityTypeManager->getStorage($type)->loadByProperties(['uuid' => $uuid]);
          $affected_entity = reset($affected_entity);

          if (!isset($broken_entities[$source_config['id']])) {
            $broken_entities[$source_config['id']] = [
              'type' => 'config entity',
              'label' => $source_config['label'],
              'id' => $source_config['id'],
            ];
          }
          if (!isset($broken_entities[$source_config['id']]['affected_entities'][$affected_entity->uuid()])) {
            $broken_entities[$source_config['id']]['affected_entities'][$affected_entity->uuid()] = [
              'type' => $type,
              'label' => $affected_entity->label(),
              'id' => $affected_entity->id(),
            ];
          }
        }
      }
    }

    return $broken_entities;
  }

  /**
   * Validates changes in json_value on entity.
   *
   * Check whether an entity using this component has content defined for a
   * field that no long exists in the component form.
   *
   * @param \Drupal\cohesion\Entity\EntityJsonValuesInterface $entity
   *   Entity with LayoutCanvas.
   * @param string $json_values
   *   New json_values in json (string) format.
   * @param string $id
   *   Entity id.
   *
   * @return bool
   *   True if entity has content references that are removed in new
   *   json_values.
   */
  protected function hasDefinedContentForRemovedComponentField(
    EntityJsonValuesInterface $entity,
    string $json_values,
    string $id,
  ): bool {
    if ($entity->isLayoutCanvas()) {
      $currentCanvas = $entity->getLayoutCanvasInstance();
      $updatedCanvas = new LayoutCanvas($json_values);

      $component_field_uuids = [];
      foreach ($updatedCanvas->iterateComponentForm() as $form_element) {
        $component_field_uuids[] = $form_element->getUUID();
      }

      foreach ($currentCanvas->iterateCanvas() as $canvas_element) {
        $element_model = $canvas_element->getModel();
        if (!$canvas_element->getComponentContentId()
          && $element_model
          && $canvas_element->getProperty('componentId') == $id
        ) {
          foreach ($element_model->getValues() as $model_key => $model_value) {
            if (preg_match(ElementModel::MATCH_UUID, $model_key) && !in_array($model_key, $component_field_uuids)) {
              return TRUE;
            }
          }
        }
      }
    }

    return FALSE;
  }

}
