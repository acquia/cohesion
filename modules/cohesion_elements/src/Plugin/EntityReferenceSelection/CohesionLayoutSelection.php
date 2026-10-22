<?php

namespace Drupal\cohesion_elements\Plugin\EntityReferenceSelection;

use Drupal\Core\Entity\Attribute\EntityReferenceSelection;
use Drupal\Core\Entity\Plugin\EntityReferenceSelection\DefaultSelection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Provides specific access control for the file entity type.
 */
#[EntityReferenceSelection(
  id: "default:cohesion_layout",
  label: new TranslatableMarkup("Site Studio layout selection"),
  group: "default",
  weight: 0,
  entity_types: ["cohesion_layout"],
)]
class CohesionLayoutSelection extends DefaultSelection {

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
