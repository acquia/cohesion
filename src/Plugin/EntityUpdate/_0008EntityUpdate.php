<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_elements\Entity\CohesionElementEntityBase;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update categories to new entities.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0008",
 * )
 */
class _0008EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function runUpdate(&$entity) {
    if ($entity instanceof CohesionElementEntityBase) {
      if ($new_category_id = $this->mapCategory($entity->getCategory(), $entity::ASSET_GROUP_ID)) {
        $entity->setCategory($new_category_id);
      }
    }

    return TRUE;
  }

  /**
   *
   */
  public function mapCategory($existing_category, $type) {
    $maps = [
      'component' => [
        'general' => 'cpt_cat_general_components',
        'layout' => 'cpt_cat_layout_components',
        'media' => 'cpt_cat_media_components',
        'interactive' => 'cpt_cat_interactive_components',
        'dynamic' => 'cpt_cat_dynamic_components',
      ],
      'helper' => [
        'general' => 'hlp_cat_general_helpers',
        'layout' => 'hlp_cat_layout_helpers',
        'media' => 'hlp_cat_media_helpers',
        'interactive' => 'hlp_cat_interactive_helpers',
        'dynamic' => 'hlp_cat_dynamic_helpers',
      ],
    ];

    if (isset($maps[$type][$existing_category])) {
      $new_category = $maps[$type][$existing_category];
      return $new_category;
    }
    else {
      return FALSE;
    }
  }

}
