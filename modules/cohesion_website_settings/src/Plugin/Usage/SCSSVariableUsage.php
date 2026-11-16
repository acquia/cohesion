<?php

namespace Drupal\cohesion_website_settings\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * SCSS variable usage plugin.
 *
 * @package Drupal\cohesion_website_settings\Plugin\Usage
 *
 * @Usage(
 *   id = "scss_variable_usage",
 *   name = @Translation("SCSS Variable usage"),
 *   entity_type = "cohesion_scss_variable",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class SCSSVariableUsage extends UsagePluginBase {

  // Use a regex to scan the JSON blob for usages for SCSS variables.
  const VARIABLE_MATCH_REGEX = '/(?!\$coh-.*$)(\$([A-Za-z0-9\_\-]*?))(?![A-Za-z0-9\_\-])/m';

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
    $variables = [];

    // Get all the SCSS variables.
    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' || $entry['type'] == 'string') {
        preg_match_all(self::VARIABLE_MATCH_REGEX, $entry['value'], $matches, PREG_SET_ORDER, 0);
        foreach ($matches as $match) {
          // Found a matching SCSS variable.
          $variables[] = $match[2];
        }
      }
    }

    if (!empty($variables)) {
      // Find matching SCSS variables.
      foreach ($variables as $variable) {
        if ($variable_entity = $this->storage->load($variable)) {
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $variable_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    return $entities;
  }

}
