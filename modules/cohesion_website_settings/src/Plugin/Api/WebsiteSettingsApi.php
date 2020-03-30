<?php

namespace Drupal\cohesion_website_settings\Plugin\Api;

use Drupal\cohesion\StylesApiPluginBase;
use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\cohesion_website_settings\Entity\WebsiteSettings;
use Drupal\Component\Serialization\Json;

/**
 * Class WebsiteSettingsApi.
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
   * @var \Drupal\cohesion_website_settings\Entity\WebsiteSettings*/
  protected $entity;

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
   */
  protected function processStyles($requestCSSTimestamp) {
    parent::processStyles($requestCSSTimestamp);

    $running_dx8_batch = &drupal_static('running_dx8_batch');

    foreach ($this->getData() as $styles) {

      if (isset($styles['css']) && $styles['themeName']) {

        $data = $styles['css'];
        // Check to see if there are actually some stylesheets to process.
        if (isset($data['base']) && !empty($data['base']) && isset($data['theme']) && !empty($data['theme']) && isset($data['master']) && !empty($data['master'])) {

          // Create admin icon library and website settings stylesheet for admin.
          $master = Json::decode($data['master']);

          if (isset($master['cohesion_website_settings']['icon_libraries']) && $this->entity instanceof IconLibrary) {
            $destination = $this->localFilesManager->getStyleSheetFilename('icons');
            if (file_unmanaged_save_data($master['cohesion_website_settings']['icon_libraries'], $destination, FILE_EXISTS_REPLACE) && !$running_dx8_batch) {
              \Drupal::logger('cohesion')
                ->notice(t(':name stylesheet has been updated', [':name' => 'icon library']));
            }
          }

          if (isset($master['cohesion_website_settings']['responsive_grid_settings']) && $this->entity instanceof WebsiteSettings && $this->entity->id() == 'responsive_grid_settings') {
            $destination = $this->localFilesManager->getStyleSheetFilename('grid');
            if (file_unmanaged_save_data($master['cohesion_website_settings']['responsive_grid_settings'], $destination, FILE_EXISTS_REPLACE) && !$running_dx8_batch) {
              \Drupal::logger('cohesion')->notice(t(':name stylesheet has been updated', [':name' => 'Responsive grid']));
            }
          }
        }
      }
    }
  }

}
