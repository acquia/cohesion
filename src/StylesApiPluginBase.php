<?php

namespace Drupal\cohesion;

use Drupal\cohesion_website_settings\Entity\WebsiteSettings;

/**
 * Site studio Plugin API for sending style to the API.
 *
 * @package Drupal\cohesion
 *
 */
abstract class StylesApiPluginBase extends ApiPluginBase {

  /**
   * Render any tokens that appear in custom styles, base style or the style
   * preview (used for media tokens).
   *
   * @param $object
   *
   * @return mixed
   */
  protected function processBackgroundImageInheritance(&$object) {

    // Handle background images inheritance.
    if (isset($object['styles'])) {
      $previous_bp = [];
      // Loop over each breakpoint in the style.
      $responsive_grid_settings = WebsiteSettings::load('responsive_grid_settings');
      $responsive_grid_json = $responsive_grid_settings->getDecodedJsonValues();
      foreach ($responsive_grid_json['breakpoints'] as $bp_key => $bp) {
        if (isset($object['styles'][$bp_key])) {
          $value = $object['styles'][$bp_key];
          $current_bp = [];
          // Check if the breakpoint has background image/gradient
          // and loop over.
          if (isset($value['background-image-settings']) && is_array($value['background-image-settings'])) {
            foreach ($value['background-image-settings'] as $key => &$background) {
              // If the current breakpoint background is a background image but
              // empty populate it with the previous breakpoint/index in the
              // array image otherwise if it has an image store it
              // for lower breakpoints.
              if (isset($background['backgroundImage']) && isset($background['backgroundLayerType']['value']) && $background['backgroundLayerType']['value'] == 'image') {
                if ((!isset($background['backgroundImage']['value']) || $background['backgroundImage']['value'] == '') && isset($previous_bp[$key])) {
                  $background['backgroundImage']['value'] = $previous_bp[$key];
                }
                else {
                  if (isset($background['backgroundImage']['value'])) {
                    $current_bp[$key] = $background['backgroundImage']['value'];
                  }
                }
              }
            }
          }
          $object['styles'][$bp_key] = $value;
          $previous_bp = $current_bp;
        }
      }
    }

    foreach ($object as $key => &$value) {
      if (is_array($value) || is_object($value)) {
        $this->processBackgroundImageInheritance($value);
      }
      else {
        $this->cohesionUtils->processTokenForApi($value);
      }
    }
  }

  /**
   * Define a 'form' element as per defined in Cohesion.
   *
   * @param string $parent
   *   md5 of the parent element.
   * @param array $children
   *   Array of md5 of children elements.
   *
   * @return array
   */
  protected function getFormElement($parent, ?array $children = NULL) {
    $form_element = [
      'parent' => $parent,
    ];
    if ($children) {
      $form_element['children'] = $children;
    }
    return $form_element;
  }

  /**
   * {@inheritdoc}
   */
  public function callApi() {
    $this->response = \Drupal::service('cohesion.api_client')->buildStyle($this->data);
  }

}
