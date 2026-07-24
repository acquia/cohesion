<?php

namespace Drupal\cohesion_website_settings\Plugin\Api;

use Drupal\cohesion\Attribute\Api;
use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\StylesApiPluginBase;
use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\cohesion_website_settings\Entity\WebsiteSettings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Website settings api plugin.
 *
 * @package Drupal\cohesion_website_settings
 *
 */
#[Api(
  id: "website_settings_api",
  name: new TranslatableMarkup("Website settings send to API"),
)]
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

    // If responsive grid not being processed continue.
    if ($this->entity->get('id') !== 'responsive_grid_settings') {
      return;
    }

    //  If the responsive grid is being processed then we call the API to get
    // the latest media queries needed for Drupal libraries.
    $response = \Drupal::service('cohesion.api_client')->buildMediaQueries($this->data);
    $activeThemeId = \Drupal::config('system.theme')->get('default');
    // Check that we have a valid response and the site has a valid
    // theme installed, if not inform the user.
    if ($this->isValidMediaQueryResponse($response, $activeThemeId, $this->cohesionUtils)) {
      $mediaQueries = json_encode(reset($response['data'])['mediaQueries']);
      $editableConfig = \Drupal::service('config.factory')->getEditable('cohesion.settings');
      // If the result is the same as what is already stored, no point
      // in saving/updating!
      if ($mediaQueries !== $editableConfig->get('media_queries')) {
        $editableConfig->set('media_queries', $mediaQueries)->save();
      }
    } else {
      $this->logger->error('No media queries returned from API, please check that you have a Site Studio enabled theme installed.');
    }
  }

  /**
   * Checks if the media query API response is valid.
   *
   * @param array $response
   *   The response array returned from the API call.
   * @param string $activeThemeId
   *   The machine name of the currently active theme.
   * @param \Drupal\cohesion\Services\CohesionUtils $utils
   *   The CohesionUtils service used to check if the theme is Cohesion enabled.
   *
   * @return bool
   *   TRUE if the response is valid and the theme has Cohesion enabled,
   *   FALSE otherwise.
   */
  private function isValidMediaQueryResponse(array $response, string $activeThemeId, CohesionUtils $utils): bool {
    return $response
      && isset($response['code']) && $response['code'] === 200
      && !empty($response['data'])
      && !empty(reset($response['data'])['mediaQueries'])
      && $utils->themeHasCohesionEnabled($activeThemeId);
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
