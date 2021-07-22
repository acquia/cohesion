<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class CohesionLayoutViewBuilder.
 *
 * Render controller for cohesion_layout.
 *
 * @package Drupal\cohesion_elements
 */
class CohesionLayoutViewBuilder extends EntityViewBuilder {

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $host = $entity->getParentEntity();
    $entities = [];
    $cache_tags = [];
    if ($host) {
      $token_type = \Drupal::service('token.entity_mapper')->getTokenTypeForEntityType($host->getEntityTypeId(), $host->getEntityTypeId());
      $entities[$token_type] = $host;

      $cache_tags[] = 'layout_formatter.' . $host->uuid();
    }

    $cacheContexts = \Drupal::service('cohesion_templates.cache_contexts');

    // Set up some variables.
    $variables = $entities;
    $variables['layout_builder_entity'] = [
      'entity' => $entity,
      'entity_type_id' => $entity->getEntityTypeId(),
      'id' => $entity->id(),
      'revision_id' => $entity->getRevisionId(),
    ];

    // Tell the field to render as a "cohesion_layout".
    $build = [
      '#type' => 'inline_template',
      '#template' => $entity->getTwig(),
      '#context' => $variables,
      '#cache' => [
        'contexts' => $cacheContexts->getFromContextName($entity->getTwigContexts()),
        'tags' => $cache_tags,
      ],
    ];

    $content = '<style>' . $entity->getStyles() . '</style>';
    $build['#attached'] = ['cohesion' => [$content]];

    return $build;
  }

}
