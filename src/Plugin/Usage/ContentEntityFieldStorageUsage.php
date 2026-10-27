<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Class ContentEntityFieldUsage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 */
#[Usage(
  id: "content_entity_field_storage_usage",
  name: new TranslatableMarkup("Content entity field storage usage"),
  entity_type: "field_storage_config",
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
class ContentEntityFieldStorageUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'field_storage_config') {
        $entities[] = [
          'type' => $this->getEntityType(),
          'uuid' => $entry['id'],
        ];
      }
    }

    return $entities;
  }

}
