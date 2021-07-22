<?php

namespace Drupal\cohesion_elements\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides specific access control for the file entity type.
 *
 * @EntityReferenceSelection(
 *   id = "default:cohesion_layout",
 *   label = @Translation("Site Studio layout selection"),
 *   entity_types = {"cohesion_layout"},
 *   group = "default",
 *   weight = 0
 * )
 */
class CohesionLayoutSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
