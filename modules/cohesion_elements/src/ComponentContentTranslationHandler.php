<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Entity\EntityInterface;
use Drupal\content_translation\ContentTranslationHandler;

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
