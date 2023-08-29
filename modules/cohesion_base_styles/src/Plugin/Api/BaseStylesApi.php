<?php

namespace Drupal\cohesion_base_styles\Plugin\Api;

use Drupal\cohesion\StylesApiPluginBase;

/**
 * Base styles api plugin.
 *
 * @package Drupal\cohesion_base_styles\Plugin\Usage
 *
 * @Api(
 *   id = "base_styles_api",
 *   name = @Translation("Base styles send to API"),
 * )
 */
class BaseStylesApi extends StylesApiPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getForms() {
    $resource = $this->entity->getResourceObject();
    $this->processBackgroundImageInheritance($resource->values);
    return [
      $this->getFormElement($resource),
    ];
  }

}
