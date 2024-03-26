<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_website_settings\Entity\Color;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update color tags label to match value.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0043",
 * )
 */
class _0043EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    // Check entity is a color.
    if ($entity instanceof Color) {
      $jsonValues = $entity->getDecodedJsonValues(TRUE);
      // Check if there are any tags.
      if (property_exists($jsonValues, 'tags')) {
        $tags = $jsonValues->tags;
        foreach ($tags as $tag) {
          // Check if the tag label & value match.
          if ($tag->label !== $tag->value) {
            // If not update label.
            $tag->label = $tag->value;
          }
        }
      }
      $entity->setJsonValue(json_encode($jsonValues));
    }

    return TRUE;
  }

}
