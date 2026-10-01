<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin for content entity field usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 */
#[Usage(
  id: "content_entity_field_usage",
  name: new TranslatableMarkup("Content entity field usage"),
  entity_type: "field_config",
  scannable: TRUE,
  scan_same_type: FALSE,
  group_key: FALSE,
  group_key_entity_type: FALSE,
  exclude_from_package_requirements: TRUE,
  exportable: FALSE,
  config_type: "core",
  can_be_excluded: FALSE,
  scan_groups: ["core"],
)]
class ContentEntityFieldUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    $scannable[] = [
      'type' => 'field_storage_config',
      'id' => $entity->getFieldStorageDefinition()->get('uuid'),
    ];

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {

      if ($entry['type'] == 'drupal_field') {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entry['uuid'],
        ];
      }
    }

    return $entities;
  }

}
