<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\block\Entity\Block;
use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for view usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "view_usage",
 *   name = @Translation("View usage"),
 *   entity_type = "view",
 *   scannable = TRUE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = FALSE,
 *   exportable = TRUE,
 *   config_type = "core",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class ViewUsage extends UsagePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getScannableData(EntityInterface $entity) {
    $scannable = [];

    if ($displays = $entity->get('display')) {
      // Loop through the displays of the view.
      foreach ($displays as $display) {

        // If it's using the Coheison formatter....
        if (isset($display['display_options']['style']['type']) && $display['display_options']['style']['type'] == 'cohesion_layout') {
          // The view template is scannable data.
          if (isset($display['display_options']['style']['options']['views_template'])) {
            $scannable[] = [
              'type' => 'entity_id',
              'entity_type' => 'cohesion_view_template',
              'id' => $display['display_options']['style']['options']['views_template'],
            ];
          }

          // The master template is scannable data.
          if (isset($display['display_options']['style']['options']['master_template'])) {
            $scannable[] = [
              'type' => 'entity_id',
              'entity_type' => 'cohesion_master_templates',
              'id' => $display['display_options']['style']['options']['master_template'],
            ];
          }
        }
      }
    }

    return $scannable;
  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);

    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' && isset($entry['decoded']['model'])) {
        // Search for components within the decoded layout canvas.
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($entry['decoded']['model']));
        foreach ($iterator as $k => $v) {
          // View used directly.
          if ($k == 'view' && $v != NULL && !is_bool($v)) {
            if ($view_entity = $this->storage->load($v)) {
              $entities[] = [
                'type' => $this->getEntityType(),
                'uuid' => $view_entity->uuid(),
              ];
            }
          }
          // View referenced as a block.
          if ($k == 'block' && strstr($v, 'views_block__')) {
            try {
              if ($dependency = Block::load($v)->getDependencies()['config'][0]) {
                if ($view_entity = $this->storage->load(str_replace('views.view.', '', $dependency))) {
                  $entities[] = [
                    'type' => $this->getEntityType(),
                    'uuid' => $view_entity->uuid(),
                  ];
                }
              }
            }
            catch (\Throwable $e) {
              continue;
            }
          }
        }
      }
    }

    return $entities;
  }

}
