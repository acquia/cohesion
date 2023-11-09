<?php

namespace Drupal\cohesion_elements;

use Drupal\content_translation\ContentTranslationHandler;
use Drupal\Core\Entity\EntityInterface;

/**
 * Class ComponentContentTranslationHandler.
 *
 * Defines the translation handler for custom blocks.
 *
 * @package Drupal\cohesion_elements
 */
class ComponentContentTranslationHandler extends ContentTranslationHandler {

  /**
   * {@inheritdoc}
   */
  protected function entityFormTitle(EntityInterface $entity) {
    return t('<em>Edit</em> @title', ['@title' => $entity->label()]);
  }

}
