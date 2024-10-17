<?php

namespace Drupal\cohesion;

use Drupal\Core\Render\Element\RenderCallbackInterface;

/**
 *
 * @see cohesion_entity_view_alter()
 */
class CohesionEntityViewBuilder implements RenderCallbackInterface {

  /**
   * Sets Cohesion - #post_render callback
   * Remove everything between <style></styles> tags for search view mode
   * results.
   *
   * @param $markup
   *
   * @return string
   */
  public static function postRender($markup) {
    $markup = preg_replace('/(<(script|style)\b[^>]*>).*?(<\/\2>)/is', "$1$3", $markup);
    return $markup;
  }

}
