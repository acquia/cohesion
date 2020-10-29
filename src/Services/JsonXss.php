<?php

namespace Drupal\cohesion\Services;

use Drupal\Core\Session\AccountInterface;
use Drupal\Component\Utility\Xss;
use Drupal\cohesion\LayoutCanvas\LayoutCanvas;

/**
 * @package Drupal\cohesion\Services
 */
class JsonXss {

  /**
   * @var \Drupal\Core\Session\AccountInterface*/
  protected $account;

  /**
   * JsonXss constructor.
   *
   * @param \Drupal\Core\Session\AccountInterface $account
   */
  public function __construct(AccountInterface $account) {
    $this->account = $account;
  }

  /**
   * Can the user bypass the Xss check?
   *
   * @return bool
   */
  public function userCanBypass() {
    return $this->account->hasPermission('bypass xss cohesion');
  }

  /**
   * Return a list of JSON paths to values that have failed Xss validation.
   *
   * @param $json_values
   *
   * @return array
   */
  public function buildXssPaths($json_values) {
    $xss_paths = [];

    $layout_canvas = new LayoutCanvas($json_values);

    foreach ($layout_canvas->iterateCanvas() as $element) {
      // Only check XSS on elements, not components.
      if (!$element->isComponent()) {
        if ($model = $element->getModel()) {
          // For each value in the element model, check for Xss directly.
          foreach ($model->getLeavesWithPathToRoot() as $leaf) {
            if (is_string($leaf['value']) && $leaf['value'] !== Xss::filterAdmin($leaf['value'])) {
              // Check for WYSIWYG.
              if (count($leaf['path']) > 2 && $leaf['path'][count($leaf['path']) - 1] == 'text' && $leaf['path'][count($leaf['path']) - 2] == 'content') {
                array_pop($leaf['path']);
              }

              $xss_paths[$model->getUUID() . '.' . implode('.', $leaf['path'])] = TRUE;
            }
          }

          // Check for Javascript event markup attributes.
          if ($markup_attributes = $model->getProperty([
            'markup',
            'attributes',
          ])) {
            foreach ($markup_attributes as $index => $property) {
              // Create a mock for <a property=attribute>.
              $mock = "<a " . addslashes($property->attribute) . "=\"" . addslashes($property->value) . "\">";
              if (Xss::filterAdmin($mock) !== $mock) {
                // If it fails validation, add both the attribute and value ot the paths so they both get disabled by the app.
                $xss_paths[$model->getUUID() . '.markup.attributes.' . $index . '.attribute'] = $property->attribute;
                $xss_paths[$model->getUUID() . '.markup.attributes.' . $index . '.value'] = $property->value;
              }
            }
          }
        }
      }
    }

    return $xss_paths;
  }

}
