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

    //  If the responsive grid is being processed then we call the API to get
    // the latest media queries needed for Drupal libraries.
    if ($this->entity->get('id') === 'responsive_grid_settings') {
      $response = \Drupal::service('cohesion.api_client')->buildMediaQueries($this->data);
      // Check we have a response & it's a 200.
      if ($response && $response['code'] === 200) {
        $mediaQueries = reset($response['data'])['mediaQueries'];
        $mediaQueries = json_encode($mediaQueries);
        // If the result is the same as what is already stored, no point
        // in saving/updating!
        if ($mediaQueries !== \Drupal::service('config.factory')->getEditable('cohesion.settings')->get('media_queries')) {
          // Set the media_queries cohesion setting.
          \Drupal::service('config.factory')->getEditable('cohesion.settings')->set('media_queries', $mediaQueries);
          // Save the config.
          \Drupal::service('config.factory')->getEditable('cohesion.settings')->save();
        }
      }
    }
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
