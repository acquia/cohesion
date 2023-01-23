<?php

namespace Drupal\cohesion_website_settings\Plugin\Api;

use Drupal\cohesion\StylesApiPluginBase;
use Drupal\cohesion_website_settings\Entity\IconLibrary;
use Drupal\cohesion_website_settings\Entity\WebsiteSettings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\File\FileSystemInterface;

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
   * @var \Drupal\cohesion_website_settings\Entity\WebsiteSettings*/
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
   */
  protected function processStyles($requestCSSTimestamp) {
    parent::processStyles($requestCSSTimestamp);

    $running_dx8_batch = &drupal_static('running_dx8_batch');

    foreach ($this->getData() as $styles) {

      if (isset($styles['css']) && $styles['themeName']) {

        $data = $styles['css'];
        // Check to see if there are actually some stylesheets to process.
        if (isset($data['master']) && !empty($data['master'])) {

          // Create admin icon library and website settings stylesheet for
          // admin.
          $master = Json::decode($data['master']);

          if (isset($master['cohesion_website_settings']['icon_libraries']) && $this->entity instanceof IconLibrary) {
            $destination = $this->localFilesManager->getStyleSheetFilename('icons');

            try {
              \Drupal::service('file_system')->saveData($master['cohesion_website_settings']['icon_libraries'], $destination, FileSystemInterface::EXISTS_REPLACE);

              if (!$running_dx8_batch) {
                \Drupal::logger('cohesion')
                  ->notice(t(':name stylesheet has been updated', [':name' => 'icon library']));
              }
            }
            catch (\Throwable $e) {
              \Drupal::messenger()->addError(t('The file could not be created.'));
            }
          }

          if (isset($master['cohesion_website_settings']['responsive_grid_settings']) && $this->entity instanceof WebsiteSettings && $this->entity->id() == 'responsive_grid_settings') {
            $destination = $this->localFilesManager->getStyleSheetFilename('grid');
            try {
              \Drupal::service('file_system')->saveData($master['cohesion_website_settings']['responsive_grid_settings'], $destination, FileSystemInterface::EXISTS_REPLACE);

              if (!$running_dx8_batch) {
                \Drupal::logger('cohesion')->notice(t(':name stylesheet has been updated', [':name' => 'Responsive grid']));
              }
            }
            catch (\Throwable $e) {
              \Drupal::messenger()->addError(t('The file could not be created.'));
            }
          }
        }
      }
    }
  }

}
