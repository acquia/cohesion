<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for view entity bundle usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "vocab_entity_bundle_usage",
 *   name = @Translation("Vocabulary entity bundle usage"),
 *   entity_type = "taxonomy_vocabulary",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core"},
 *   can_be_excluded = FALSE
 * )
 */
class VocabEntityBundleUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Get taxonomy fields.
    $vocab_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('taxonomy_term', $entity->get('vid'));

    foreach ($vocab_fields as $field) {
      $scannable[] = [
        'type' => 'drupal_field',
        'entity_type' => 'field_config',
        'uuid' => $field->getUniqueIdentifier(),
      ];
    }

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'drupal_field') {

        // Load the field.
        $field = $this->drupalFieldLoad($entry['uuid']);

        // Check if the field is an entity reference and references taxonomy
        // terms.
        if (!empty($field) && $field->getType() == 'entity_reference') {
          if ($field->getSetting('handler') == 'default:taxonomy_term') {
            $handler_settings = $field->getSetting('handler_settings');

            foreach ($handler_settings['target_bundles'] as $bundle) {
              if ($vocab_uuid = $this->taxonomyVocabularyBundleLoad($bundle)) {

                $entities[] = [
                  'type' => $this->getEntityType(),
                  'uuid' => $vocab_uuid,
                ];
              }
            }
          }
        }
      }

      if ($entry['type'] == 'taxonomy_vocabulary') {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entry['uuid'],
        ];
      }
    }

    return $entities;
  }

  /**
   * @param $bundle
   *
   * @return mixed
   */
  public function taxonomyVocabularyBundleLoad($bundle) {
    $vocab = \Drupal::service('entity_type.manager')->getStorage('taxonomy_vocabulary')->load($bundle);
    if($vocab) {
      return $vocab->uuid();
    }
    return FALSE;
  }

  /**
   * @param $entry
   *
   * @return mixed
   */
  public function drupalFieldLoad($entry) {
    return \Drupal::service('entity.repository')->loadEntityByUuid('field_config', $entry);
  }

}
