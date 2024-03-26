<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Entity\EntityInterface;

/**
 * Plugin for image style usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "imagestyle_usage",
 *   name = @Translation("Image style usage"),
 *   entity_type = "image_style",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = TRUE,
 *   config_type = "core",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = TRUE
 * )
 */
class ImageStyleUsage extends UsagePluginBase {

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
      if ($entry['type'] == 'json_string' && isset($entry['decoded']['model'])) {
        // Search for components within the decoded layout canvas.
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($entry['decoded']['model']));
        foreach ($iterator as $k => $v) {
          if ($k == 'imageStyle' && $v != NULL) {

            // Load the image style to get its UUID.
            if ($image_style_entity = $this->storage->load($v)) {
              $entities[] = [
                'type' => $this->getEntityType(),
                'uuid' => $image_style_entity->uuid(),
                // 'subid' => NULL,
                // 'url' => $view_entity->toUrl('edit-form')
              ];
            }
          }
        }
      }
    }

    return $entities;
  }

}
