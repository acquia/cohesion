<?php

namespace Drupal\cohesion_templates\ConfigTranslation;

use Drupal\config_translation\ConfigEntityMapper;

/**
 * Provides a configuration mapper for cohesion content templates.
 */
class CohesionTemplatesMapper extends ConfigEntityMapper {

  /**
   * {@inheritdoc}
   */
  public function getBaseRouteParameters() {
    $parameters = parent::getBaseRouteParameters();

    $entity = $this->entity;
    $parameters['content_entity_type'] = $entity->get('entity_type');

    return $parameters;

  }

}
