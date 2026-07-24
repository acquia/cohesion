<?php

namespace Drupal\cohesion\Hook;

use Drupal\cohesion_elements\Entity\CohesionLayout;
use Drupal\cohesion_elements\Plugin\Field\FieldType\CohesionEntityReferenceRevisionsItem;
use Drupal\Component\Uuid\Uuid;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\JobItemInterface;

/**
 * TMGMT Hook implementations for Cohesion.
 */
class CohesionTmgmtHooks {

  public function __construct(
    protected EntityTypeManagerInterface $entity_type_manager,
  ) {}

  /**
   * Implements hook_tmgmt_translatable_fields_alter().
   */
  #[Hook('tmgmt_translatable_fields_alter')]
  public function tmgmt_translatable_fields_alter(&$entity, &$translatable_fields): void {
    if ($entity instanceof CohesionLayout && isset($translatable_fields['json_values'])) {
      $translatable_fields = [
        'json_values' => $translatable_fields['json_values'],
      ];
    }
  }

  /**
   * Implements hook_tmgmt_source_suggestions().
   */
  #[Hook('tmgmt_source_suggestions')]
  public function tmgmt_source_suggestions(array $items, JobInterface $job): array {
    $suggestions = [];

    foreach ($items as $item) {
      if ($item instanceof JobItemInterface && $item->getPlugin() == 'content') {
        // Load the entity, skip if it can't be loaded.
        $entity = $this->entity_type_manager
          ->getStorage($item->getItemType())
          ->load($item->getItemId());
        if (!($entity instanceof ContentEntityInterface)) {
          continue;
        }

        foreach ($entity as $field) {
          /** @var \Drupal\Core\Field\FieldItemListInterface $field */
          $definition = $field->getFieldDefinition();

          // Skip fields that are already embedded.
          if (isset($embedded_fields[$definition->getName()])) {
            continue;
          }

          // Loop over all field items.
          foreach ($field as $field_item) {
            if ($field_item instanceof CohesionEntityReferenceRevisionsItem) {
              // Loop over all properties of a field item.
              foreach ($field_item->getProperties(TRUE) as $property) {
                if ($property->getValue() instanceof CohesionLayout) {
                  /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $layout_canvas_entity */
                  $layout_canvas_entity = $property->getValue();
                  $layout_canvas = $layout_canvas_entity->getLayoutCanvasInstance();

                  foreach ($layout_canvas->getEntityReferences() as $reference) {
                    if (Uuid::isValid($reference['entity_id'])) {
                      $results = $this->entity_type_manager
                        ->getStorage($reference['entity_type'])
                        ->loadByProperties(['uuid' => $reference['entity_id']]);
                      $target = reset($results);
                    }
                    else {
                      $target = $this->entity_type_manager
                        ->getStorage($reference['entity_type'])
                        ->load($reference['entity_id']);
                    }
                    if ($target instanceof EntityInterface && $target instanceof TranslatableInterface) {
                      $enabled = \Drupal::service('content_translation.manager')->isEnabled($target->getEntityTypeId(), $target->bundle());
                      if ($enabled && $target->hasTranslation($job->getSourceLangcode())) {
                        $suggestions[] = [
                          'job_item' => tmgmt_job_item_create('content', $target->getEntityTypeId(), $target->id()),
                          'reason' => t('Field @label', ['@label' => $definition->getLabel()]),
                          'from_item' => $item->id(),
                        ];
                      }
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    return $suggestions;
  }

}
