<?php

namespace Drupal\cohesion_templates\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Menu templates usage plugin.
 *
 * @package Drupal\cohesion_templates\Plugin\Usage
 *
 * @Usage(
 *   id = "menu_templates_usage",
 *   name = @Translation("Menu templates usage"),
 *   entity_type = "cohesion_menu_templates",
 *   scannable = TRUE,
 *   scan_same_type = TRUE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "site_studio",
 *   scan_groups = {"site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class MenuTemplatesUsage extends UsagePluginBase {

  /**
   * @var array
   */
  private $entities;

  /**
   * Search for menu templates in the decoded JSON layout.
   * Implemented as a recursive function because the RecursiveIterator
   * only visits leaf nodes :/.
   *
   * @param $array
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function scanForMenuTemplates($array) {
    foreach ($array as $key => $item) {
      if ($key == 'menu' && isset($item['template'])) {
        // Load the menu template to get it's UUID.
        if ($menu_template_entity = $this->storage->load($item['template'])) {
          // Add to the list of dependencies.
          $this->entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $menu_template_entity->uuid(),
            'subid' => NULL,
          ];
        }
      }

      if (is_array($item)) {
        // Recurse...
        $this->scanForMenuTemplates($item);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    // The JSON values and the master template.
    return [
      [
        'type' => 'json_string',
        'value' => $entity->getJsonValues(),
        'decoded' => $entity->getDecodedJsonValues(),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $this->entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' && isset($entry['decoded']['model'])) {
        $this->scanForMenuTemplates($entry['decoded']['model']);
      }
    }

    return $this->entities;
  }

}
