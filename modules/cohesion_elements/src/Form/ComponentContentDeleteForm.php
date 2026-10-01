<?php

namespace Drupal\cohesion_elements\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Builds the form to delete Site Studio custom styles entities.
 */
class ComponentContentDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    /** @var \Drupal\cohesion_elements\ComponentContentInterface $entity */
    $entity = $this->getEntity();
    $this->logger('content')->notice('@type: deleted %title.',
      [
        '@type' => $entity->getEntityType()->getLabel(),
        '%title' => $entity->label(),
      ]
    );
  }

}
