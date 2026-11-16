<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update available in CKEditor
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0039",
 * )
 */
class _0039EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof CustomStyle) {
      $entity->set('available_in_wysiwyg_inline', $entity->get('available_in_wysiwyg'));

      // Generic custom styles can only be added as inline style with CKE5
      if ($entity->get('custom_style_type') == 'generic') {
        $entity->set('available_in_wysiwyg', FALSE);
      }
    }

    return TRUE;
  }

}
