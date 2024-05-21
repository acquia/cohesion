<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for media entity bundle usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "media_entity_bundle_usage",
 *   name = @Translation("Media entity bundle usage"),
 *   entity_type = "media_type",
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
class MediaEntityBundleUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Get media entity fields.
    $bundle_fields = \Drupal::service('entity_field.manager')->getFieldDefinitions('media', $entity->get('id'));

    foreach ($bundle_fields as $field) {
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

        // Check if the field is an entity reference and references media types.
        if (!empty($field) && $field->getType() == 'entity_reference') {
          if ($field->getSetting('handler') == 'default:media') {
            $handler_settings = $field->getSetting('handler_settings');

            // Get each media type that is set on the field.
            foreach ($handler_settings['target_bundles'] as $bundle) {
              $media_type_uuid = $this->mediaEntityBundleLoad($bundle);

              $entities[] = [
                'type' => $this->getEntityType(),
                'uuid' => $media_type_uuid,
              ];
            }
          }
        }
      }

      if ($entry['type'] == 'media_type') {
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
  public function mediaEntityBundleLoad($bundle) {
    $media_type = \Drupal::service('entity_type.manager')->getStorage('media_type')->load($bundle);
    return $media_type->uuid();
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
