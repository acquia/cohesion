<?php

namespace Drupal\cohesion\Plugin\Api;

use Drupal\cohesion\ApiPluginBase;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\Component\Serialization\Json;

/**
 * Class StyleApi.
 *
 * @package Drupal\cohesion
 *
 * @Api(
 *   id = "styles_api",
 *   name = @Translation("Styles send to API"),
 * )
 */
class StylesApi extends ApiPluginBase {

  /**
   * The style forms to be processed by the API.
   *
   * @var array
   */
  private $forms = [];

  /**
   *
   */
  public function getForms() {
    return $this->forms;
  }

  /**
   *
   */
  public function setForms($forms) {
    $this->forms = $forms;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareData($attach_css = TRUE) {
    parent::prepareData($attach_css);

    // Reorder custom style styles.
    $custom_styles = CustomStyle::loadParentChildrenOrdered();
    $style_order = [];
    if ($custom_styles) {
      foreach ($custom_styles as $custom_style) {
        $key = $custom_style->id() . '_' . $custom_style->getConfigItemId();
        $style_order[] = $key;
      }
    }

    $this->data->sort_order = $style_order;
    $this->data->style_group = 'cohesion_custom_style';
  }

  /**
   * {@inheritdoc}
   */
  public function callApi() {
    $this->response = \Drupal::service('cohesion.api_client')->buildStyle($this->data);
  }

  /**
   * @inheritDoc
   *
   *  Process icon libraries & responsive grid CSS files - on rebuild.
   */
  protected function processStyles($requestCSSTimestamp) {
    parent::processStyles($requestCSSTimestamp);

    foreach ($this->getData() as $styles) {
      if (isset($styles['css']) && $styles['themeName']) {
        $data = Json::decode($styles['css']);
        // Check to see if there are actually some stylesheets to process.
        if (isset($data['styles'])) {
          // Create icon library and website settings stylesheet for admin.
          $stylesDiff = Json::decode($styles['css'])['styles'];

          if (isset($stylesDiff['added']['cohesion_website_settings']) || isset($stylesDiff['updated']['cohesion_website_settings'])) {
            if (isset($stylesDiff['added']['cohesion_website_settings']['icon_libraries']) || isset($stylesDiff['updated']['cohesion_website_settings']['icon_libraries'])) {
              $this->processWebsiteSettingsDiff($stylesDiff, 'icon_libraries', 'icons', 'Icon library');
            }

            if (isset($stylesDiff['added']['cohesion_website_settings']['responsive_grid_settings']) || isset($stylesDiff['updated']['cohesion_website_settings']['responsive_grid_settings'])) {
              $this->processWebsiteSettingsDiff($stylesDiff, 'responsive_grid_settings', 'grid', 'Responsive grid');
            }
          }
        }
      }
    }
  }

}
