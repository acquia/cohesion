<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for webform usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "webform",
 *   name = @Translation("Webform usage"),
 *   entity_type = "webform",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class WebformUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    // Check for classes added to elements on the webform.
    $scannable[] = [
      'type' => 'string',
      'value' => $entity->get('elements'),
    ];

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    return [];
  }

}
