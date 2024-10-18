<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update background color for custom & base style previews
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0040",
 * )
 */
class _0040EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['id'];
  }

  /**
   *  Convert colour HEX code to RGBA format.
   *  From here: https://stackoverflow.com/questions/15202079/convert-hex-color-to-rgb-values-in-php
   *
   * @param $hex
   * @param bool $alpha
   * @return array
   */
  public function hexToRgb($hex, bool $alpha = FALSE): array {
    $hex = str_replace('#', '', $hex);
    $length = strlen($hex);
    $rgb['r'] = hexdec($length == 6 ? substr($hex, 0, 2) : ($length == 3 ? str_repeat(substr($hex, 0, 1), 2) : 0));
    $rgb['g'] = hexdec($length == 6 ? substr($hex, 2, 2) : ($length == 3 ? str_repeat(substr($hex, 1, 1), 2) : 0));
    $rgb['b'] = hexdec($length == 6 ? substr($hex, 4, 2) : ($length == 3 ? str_repeat(substr($hex, 2, 1), 2) : 0));
    if ($alpha) {
      $rgb['a'] = $alpha;
    }
    return $rgb;
  }

  /**
   * {@inheritdoc}
   */
  public function runUpdate(&$entity) {
    if ($entity instanceof EntityJsonValuesInterface) {
      if (!$entity->isLayoutCanvas() && $json_values = $entity->getDecodedJsonValues(TRUE)) {
        if (property_exists($json_values, 'sBackgroundColour')) {
          // Check if it's a string (in most cases it should be)
          if (is_string($json_values->sBackgroundColour)) {
            $bg_color = $this->hexToRgb($json_values->sBackgroundColour);
          } else {
            // If it's an object get the value.
            $bg_color = $this->hexToRgb($json_values->sBackgroundColour->value->hex);
          }
          // Format the background colour as React wants.
          $new_formatted_colour = 'rgba(' . $bg_color['r'] . ',' . $bg_color['g'] . ',' . $bg_color['b'] . ',' . '1' . ')';
          // Check if the preview property exists as sometimes it may not.
          if (!property_exists($json_values, 'preview')) {
            $json_values->preview = new \stdClass();
          }
          // Set the background colour in the correct place in the JSON.
          $json_values->preview->background = new \stdClass();
          $json_values->preview->background->value = [
            'rgba' => $new_formatted_colour,
          ];

          //Remove sBackground property;
          unset($json_values->sBackgroundColour);
        }

        $entity->setJsonValue(json_encode($json_values));
      }
    }

    return TRUE;
  }

}
