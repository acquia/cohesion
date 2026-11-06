<?php

namespace Drupal\cohesion_website_settings\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Website settings entity base.
 *
 * @package Drupal\cohesion_website_settings\Entity
 */
abstract class WebsiteSettingsEntityBase extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  /**
   * {@inheritdoc}
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('website_settings_api');
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {

    // Load the locked state form JSON for entities with an Angular form.
    if ($decoded_json_values = $this->getDecodedJsonValues(FALSE)) {
      if (isset($decoded_json_values['locked'])) {
        $this->setLocked($decoded_json_values['locked']);
      }
    }

    parent::preSave($storage);
  }

}
