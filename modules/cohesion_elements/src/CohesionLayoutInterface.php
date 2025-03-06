<?php

namespace Drupal\cohesion_elements;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\entity_reference_revisions\EntityNeedsSaveInterface;

/**
 * Provides an interface defining a paragraphs entity.
 *
 * @ingroup paragraphs
 */
interface CohesionLayoutInterface extends ContentEntityInterface, EntityNeedsSaveInterface {

  /**
   * Gets the parent entity of the paragraph.
   *
   * Preserves language context with translated entities.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface
   *   The parent entity.
   */
  public function getParentEntity();

}
