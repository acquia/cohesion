<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_website_settings\Entity\FontLibrary;
use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update Font and Icon libraries cohesion stream wrapper.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0022",
 * )
 */
class _0022EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof FontLibrary || $entity instanceof IconLibrary) {
      $this->updateLibrary($entity);
    }

    return TRUE;

  }

  /**
   *
   */
  public function updateLibrary(&$entity) {
    $json_values = $entity->getDecodedJsonValues(TRUE);

    if (property_exists($json_values, 'fontFiles') && is_object($json_values->fontFiles)) {
      foreach ($json_values->fontFiles as &$fontFile) {
        if (is_object($fontFile) && property_exists($fontFile, 'uri') && strpos($fontFile->uri, 'cohesion://') !== FALSE) {
          $fontFile->uri = str_replace('cohesion://', 'public://cohesion/', $fontFile->uri);
        }
      }
    }

    if (property_exists($json_values, 'iconJSON') && is_object($json_values->iconJSON) &&
      property_exists($json_values->iconJSON, 'json') && strpos($json_values->iconJSON->json, 'cohesion://') !== FALSE) {
      $json_values->iconJSON->json = str_replace('cohesion://', 'public://cohesion/', $json_values->iconJSON->json);
    }

    $entity->setJsonValue(json_encode($json_values));
  }

}
