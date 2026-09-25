<?php

namespace Drupal\cohesion_elements\Plugin\Usage;

use Drupal\cohesion\Attribute\Usage;
use Drupal\cohesion\UsagePluginBase;
use Drupal\cohesion_elements\Entity\ComponentTag;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Component tag usage plugin.
 *
 * @package Drupal\cohesion_elements\Plugin\Usage
 */
#[Usage(
  id: "cohesion_component_tag_usage",
  name: new TranslatableMarkup("Component tag usage"),
  entity_type: "cohesion_component_tag",
  scannable: FALSE,
  scan_same_type: FALSE,
  group_key: FALSE,
  group_key_entity_type: FALSE,
  exclude_from_package_requirements: FALSE,
  exportable: TRUE,
  config_type: "site_studio",
  can_be_excluded: TRUE,
  scan_groups: ["site_studio"],
)]
class ComponentTagUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'tag_id') {
        foreach (ComponentTag::loadMultiple($entry['id']) as $entity) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
