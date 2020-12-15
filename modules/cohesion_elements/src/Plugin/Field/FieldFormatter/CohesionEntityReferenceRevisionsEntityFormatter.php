<?php

namespace Drupal\cohesion_elements\Plugin\Field\FieldFormatter;

use Drupal\entity_reference_revisions\Plugin\Field\FieldFormatter\EntityReferenceRevisionsEntityFormatter;

/**
 * Plugin implementation of the 'entity reference rendered entity' formatter.
 *
 * @FieldFormatter(
 *   id = "cohesion_entity_reference_revisions_entity_view",
 *   label = @Translation("Site Studio rendered entity"),
 *   description = @Translation("Display the referenced entities rendered by entity_view()."),
 *   field_types = {
 *     "cohesion_entity_reference_revisions"
 *   }
 * )
 */
class CohesionEntityReferenceRevisionsEntityFormatter extends EntityReferenceRevisionsEntityFormatter {


}
