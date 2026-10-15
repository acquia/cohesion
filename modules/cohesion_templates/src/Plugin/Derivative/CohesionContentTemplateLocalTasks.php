<?php

namespace Drupal\cohesion_templates\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;

/**
 * Class CohesionContentTemplateLocalTasks.
 *
 * Defines dynamic local tasks.
 *
 * @package Drupal\cohesion_templates\Plugin\Derivative
 */
class CohesionContentTemplateLocalTasks extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {

    $templates_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
      ->accessCheck(TRUE)
      ->execute();

    if ($templates_ids) {
      $entity_types = \Drupal::entityTypeManager()->getDefinitions();
      $candidate_template_storage = \Drupal::entityTypeManager()->getStorage('cohesion_content_templates');
      $candidate_templates = $candidate_template_storage->loadMultiple($templates_ids);
      foreach ($candidate_templates as $entity) {
        if (!isset($this->derivatives[$entity->get('entity_type')])) {
          if (isset($entity_types[$entity->get('entity_type')])) {
            $entity_type = $entity_types[$entity->get('entity_type')];
            $this->derivatives[$entity->get('entity_type')] = $base_plugin_definition;
            $this->derivatives[$entity->get('entity_type')]['title'] = ($entity_type->get('bundle_label')) ? $entity_type->get('bundle_label') : $entity_type->get('label');
            $this->derivatives[$entity->get('entity_type')]['route_name'] = 'entity.cohesion_content_templates.collection';
            $this->derivatives[$entity->get('entity_type')]['base_route'] = 'entity.cohesion_content_templates.collection';
            $this->derivatives[$entity->get('entity_type')]['route_parameters'] = ['content_entity_type' => $entity->get('entity_type')];
          }
        }
      }

    }
    return $this->derivatives;
  }

}
