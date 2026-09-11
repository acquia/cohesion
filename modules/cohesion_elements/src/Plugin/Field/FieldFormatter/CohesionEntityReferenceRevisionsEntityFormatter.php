<?php

namespace Drupal\cohesion_elements\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\Attribute\FieldFormatter;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 */
#[FieldFormatter(
  id: "cohesion_entity_reference_revisions_entity_view",
  label: new TranslatableMarkup("Site Studio rendered entity"),
  description: new TranslatableMarkup("Display the referenced entities rendered by entity_view()."),
  field_types: ["cohesion_entity_reference_revisions"]
)]
class CohesionEntityReferenceRevisionsEntityFormatter extends EntityReferenceRevisionsEntityFormatter {

  /**
   * {@inheritdoc}
   *
   * @see ::prepareView()
   * @see ::getEntitiestoView()
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);
    return $elements;
  }

}
