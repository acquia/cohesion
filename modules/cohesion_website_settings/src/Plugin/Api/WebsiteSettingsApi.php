<?php

namespace Drupal\cohesion_website_settings\Plugin\Api;

use Drupal\cohesion\StylesApiPluginBase;
use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\cohesion_website_settings\Entity\WebsiteSettings;
use Drupal\Component\Serialization\Json;

/**
 * Website settings api plugin.
 *
 * @package Drupal\cohesion_website_settings
 *
 * @Api(
 *   id = "website_settings_api",
 *   name = @Translation("Website settings send to API"),
 * )
 */
class WebsiteSettingsApi extends StylesApiPluginBase {

  /**
   * @var \Drupal\cohesion_website_settings\Entity\WebsiteSettings
   */
  protected $entity;

  /**
   *
   */
  public function getForms() {
    return [
      $this->getFormElement($this->entity->getResourceObject()),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function prepareData($attach_css = TRUE) {
    parent::prepareData();
  }

  /**
   * @inheritDoc
   *
   * Process icon libraries & responsive grid CSS files - on save of entity.
   */
  protected function processStyles($requestCSSTimestamp) {
    parent::processStyles($requestCSSTimestamp);

    foreach ($this->getData() as $styles) {
      if (isset($styles['css']) && $styles['themeName']) {
        $stylesDiff = Json::decode($styles['css'])['styles'];

        if (isset($stylesDiff['added']['cohesion_website_settings']['icon_libraries']) || isset($stylesDiff['updated']['cohesion_website_settings']['icon_libraries']) && $this->entity instanceof IconLibrary) {
          $this->processWebsiteSettingsDiff($stylesDiff, 'icon_libraries', 'icons', 'Icon library');
        }

        if (isset($stylesDiff['added']['cohesion_website_settings']['responsive_grid_settings']) || isset($stylesDiff['updated']['cohesion_website_settings']['responsive_grid_settings']) && $this->entity instanceof WebsiteSettings && $this->entity->id() == 'responsive_grid_settings') {
          $this->processWebsiteSettingsDiff($stylesDiff, 'responsive_grid_settings', 'grid', 'Responsive grid');
        }
      }
    }
  }

}
