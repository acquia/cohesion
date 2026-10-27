<?php

namespace Drupal\cohesion_elements\Plugin\Field\FieldFormatter;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityInterface;
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

  /**
   * {@inheritdoc}
   *
   * The layout's own view access is not re-checked here. It delegates to the
   * owning entity, which wrongly denies owners rendered without a view
   * permission, such as menu links ('administer menu') and content blocks.
   * The field's view access is still enforced by the view builder, but the
   * owner's entity-level access is not: callers rendering this field must
   * check the owner's access themselves, as entity, menu and block rendering
   * already do. Direct reads of the layout entity, e.g. via JSON:API, still go
   * through the access handler.
   */
  protected function checkAccess(EntityInterface $entity) {
    return AccessResult::allowed();
  }

}
