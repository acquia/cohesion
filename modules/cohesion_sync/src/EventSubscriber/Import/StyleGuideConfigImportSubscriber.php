<?php

namespace Drupal\cohesion_sync\EventSubscriber\Import;

use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;
use Drupal\cohesion_style_guide\Entity\StyleGuideManager;

/**
 * Cohesion Config import subscriber for handling StyleGuide validation.
 *
 * @package Drupal\cohesion_sync\EventSubscriber
 */
class StyleGuideConfigImportSubscriber extends CohesionConfigImportSubscriberBase {

  const CONFIG_PREFIX = 'cohesion_style_guide.cohesion_style_guide.';

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
      if ($type === 'cohesion_style_guide_manager') {
        $entity = $this->entityTypeManager->getStorage($type)
          ->loadByProperties(['uuid' => $uuid]);
        $entity = reset($entity);

        if ($entity instanceof StyleGuideManager) {
          $updatedCanvas = new LayoutCanvas($source_config['json_values']);

          $component_field_uuids = [];
          foreach ($updatedCanvas->iterateComponentForm() as $form_element) {
            $component_field_uuids[] = $form_element->getUUID();
          }

          $json_values = $entity->getDecodedJsonValues();
          if (isset($json_values['model'][$target_config['uuid']])) {
            foreach ($json_values['model'][$target_config['uuid']] as $model_key => $model_value) {
              if (preg_match(ElementModel::MATCH_UUID, $model_key)
                && !in_array($model_key, $component_field_uuids)
              ) {
                if (!isset($broken_entities[$source_config['id']])) {
                  $broken_entities[$source_config['id']] = [
                    'type' => $source_config['type'] ?? $entity->getEntityTypeId(),
                    'label' => $source_config['label'],
                    'id' => $source_config['id'],
                  ];
                }
                if (!isset($broken_entities[$source_config['id']]['affected_entities'][$entity->uuid()])) {
                  $broken_entities[$source_config['id']]['affected_entities'][$entity->uuid()] = [
                    'type' => $type,
                    'label' => $entity->label(),
                    'id' => $entity->id(),
                  ];
                }
              }
            }
          }
        }
      }
    }

    return $broken_entities;
  }

}
