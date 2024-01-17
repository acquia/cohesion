<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\field\Entity\FieldConfig;

/**
 *
 */
abstract class FieldableContentEntityUsageBase extends UsagePluginBase {

  /**
   * Get the template ID from the template selector value.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $template
   *
   * @return mixed
   */
  private function getContentTemplateId(EntityInterface $entity, $template) {

    if ($template != '__default__') {
      // Not a default template, so just make sure the template exists.
      $template_result = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')
        ->getQuery()
        ->accessCheck(TRUE)
        ->condition('id', $template)
        ->execute();
    }
    else {
      // Default, so query for the default of this entity type/bundle.
      $template_result = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')
        ->getQuery()
        ->accessCheck(TRUE)
        ->condition('entity_type', $entity->getEntityTypeId())
        ->condition('bundle', $entity->bundle())
        ->condition('view_mode', 'full')
        ->condition('default', TRUE)
        ->execute();
    }

    // The template exists.
    if (count($template_result)) {
      return array_shift($template_result);
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Loop through the fields, searching for cohesion_layout or WYSIWYG.
    foreach ($entity->getFieldDefinitions() as $field) {
      if ($field instanceof FieldConfig) {

        // cohesion_layout reference.
        if ($field->getSetting('handler') == 'default:cohesion_layout') {
          /** @var \Drupal\Core\Entity\ContentEntityInterface $item */
          foreach ($entity->get($field->getName()) as $item) {
            try {
              $target_id = $item->getValue()['target_id'];
              if (!is_null($item->getValue()['target_id'])) {
                /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $cohesion_layout_entity */
                $cohesion_layout_entity = $this->entityTypeManager->getStorage('cohesion_layout')->load($target_id);

                $scannable[] = [
                  'type' => 'json_string',
                  'value' => $cohesion_layout_entity->getJsonValues(),
                  'decoded' => $cohesion_layout_entity->getDecodedJsonValues(),
                ];
              }
            }
            catch (\Exception $e) {
              break;
            }
          }
          continue;
        }

        // Content template selector.
        if ($field->getType() == 'cohesion_template_selector') {
          // Get the string value of the selected template.
          if ($selected_template = $entity->get($field->getName())->getString()) {
            // Validate this is actually an entity.
            if ($content_template_id = $this->getContentTemplateId($entity, $selected_template)) {
              // Add it to the list of scannable data.
              $scannable[] = [
                'type' => 'entity_id',
                'entity_type' => 'cohesion_content_templates',
                'id' => $content_template_id,
              ];
            }
          }

          continue;
        }

        // WYSIWYG / longtext.
        if ($field->getType() !== 'layout_section') {
          $field_data = $entity->get($field->getName());
          $string = $field_data->getString();

          if ($string && !empty($string)) {
            $scannable[] = [
              'type' => 'string',
              'value' => $string,
            ];
          }
        }
      }
    }

    // Return everything.
    return $scannable;
  }

}
