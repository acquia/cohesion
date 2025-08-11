<?php

namespace Drupal\cohesion\Entity;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Tags;
use Drupal\Core\Entity\EntityAutocompleteMatcher;
use Drupal\Core\Entity\EntityPublishedInterface;
use Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Class CohesionEntityAutocompleteMatcher.
 *
 * Matcher class to get autocompletion results for entity reference.
 *
 * @package Drupal\cohesion\Entity
 */
class CohesionEntityAutocompleteMatcher extends EntityAutocompleteMatcher {

  /**
   * The entity reference selection handler plugin manager.
   *
   * @var \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface
   */
  protected $selectionManager;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a EntityAutocompleteMatcher object.
   *
   * @param \Drupal\Core\Entity\EntityReferenceSelection\SelectionPluginManagerInterface $selection_manager
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *
   *   The entity reference selection handler plugin manager.
   */
  public function __construct(SelectionPluginManagerInterface $selection_manager, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($selection_manager);

    $this->entityTypeManager = $entityTypeManager;

  }

  /**
   * {@inheritdoc}
   */
  public function getMatches($target_type, $selection_handler, $selection_settings, $string = '') {
    $matches = [];

    $options = $selection_settings + [
      'target_type' => $target_type,
      'handler' => $selection_handler,
    ];
    $handler = $this->selectionManager->getInstance($options);

    if (isset($string)) {
      // Get an array of matching entities.
      $match_operator = !empty($selection_settings['match_operator']) ? $selection_settings['match_operator'] : 'CONTAINS';
      $entity_labels = $handler->getReferenceableEntities($string, $match_operator, 50);

      // Loop through the entities and convert them into autocomplete output.
      foreach ($entity_labels as $values) {
        foreach ($values as $entity_id => $label) {
          $entity = $this->entityTypeManager->getStorage($target_type)->load($entity_id);
          // Only show published content & account for entities that don't have
          // an Entity published interface.
          if(($entity instanceof EntityPublishedInterface) && $entity->isPublished() || !($entity instanceof EntityPublishedInterface)) {

            $key = "$label ($entity_id)";
            // Strip things like starting/trailing white spaces, line breaks and
            // tags.
            $key = preg_replace('/\s\s+/', ' ', str_replace("\n", '', trim(Html::decodeEntities(strip_tags($key)))));
            // Names containing commas or quotes must be wrapped in quotes.
            $key = Tags::encode($key);
            $matches[] = ['value' => $key, 'label' => $label];
          }
        }
      }
    }

    return $matches;
  }

}
