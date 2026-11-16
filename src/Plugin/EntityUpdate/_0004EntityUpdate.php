<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Convert all /settings-endpoint/ strings to /cohesionapi/.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0004",
 * )
 */
class _0004EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof EntityJsonValuesInterface) {
      $json = $entity->getJsonValues();
      $new_json = str_replace('/settings-endpoint', '/cohesionapi', $json);

      // Only apply is changed.
      if ($json !== $new_json) {
        $entity->setJsonValue($new_json);
      }
    }

    return TRUE;
  }

}
