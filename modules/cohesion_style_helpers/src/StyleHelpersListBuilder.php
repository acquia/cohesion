<?php

namespace Drupal\cohesion_style_helpers;

use Drupal\cohesion\CohesionListBuilder;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Utility\Error;

/**
 * Class StyleHelpersListBuilder.
 *
 * Provides a listing of style helper entities.
 *
 * @package Drupal\cohesion_style_helpers
 */
class StyleHelpersListBuilder extends CohesionListBuilder {

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row = parent::buildRow($entity);

    try {
      $type_entity = \Drupal::entityTypeManager()->getStorage('custom_style_type')->load($entity->getCustomStyleType());
    }
    catch (PluginNotFoundException $ex) {
      Error::logException('cohesion', $ex);
      $type_entity = NULL;
    }
    $row['type'] = $type_entity ? $type_entity->label() : NULL;

    return $row;
  }

}
