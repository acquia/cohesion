<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update tree model.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0028",
 * )
 */
class _0028EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function runUpdate(&$entity) {

    if ($entity instanceof EntityJsonValuesInterface && !$entity->isLayoutCanvas()) {

      // Update Custom styles / Base styles / Style helpers.
      $json_values = $entity->getDecodedJsonValues(TRUE);
      $json_mapper = json_decode($entity->getJsonMapper(), TRUE);

      if (isset($json_mapper['styles']['items']) && is_array($json_mapper['styles']['items'])) {
        $new_css_children = $this->proccessMapper($json_mapper['styles']['items']);

        if (property_exists($json_values, 'variableFields') && is_array($json_values->variableFields)) {
          foreach ($json_values->variableFields as &$variableField) {
            foreach ($new_css_children as $new_child_key => $uuid) {
              if (strpos($variableField, 'styles' . $new_child_key . '.styles') === 0) {
                $variableField = str_replace('styles' . $new_child_key . '.styles', 'styles.' . $uuid . '.styles', $variableField);
              }
            }
          }
        }
      }

      $entity->setJsonValue(json_encode($json_values));
    }

    return TRUE;
  }

  /**
   *
   */
  private function proccessMapper($items, $path = '') {

    $new_css_children = [];

    $selectors = [
      'child' => 0,
      'pseudo' => 0,
      'modifier' => 0,
      'prefix' => 0,
    ];

    $selectorNames = [
      'child' => 'children',
      'pseudo' => 'pseudos',
      'modifier' => 'modifiers',
      'prefix' => 'prefix',
    ];

    foreach ($items as $item) {
      if (isset($item['selectorType']) && isset($item['uuid'])) {
        foreach ($selectors as $selectorType => &$selector) {
          if ($item['selectorType'] == $selectorType) {
            $new_path = $path . '.' . $selectorNames[$selectorType] . '.' . $selector;
            $selector = $selector + 1;
            $new_css_children[$new_path] = $item['uuid'];

            if (isset($item['items']) && is_array($item['items'])) {
              $new_css_children = array_merge($new_css_children, $this->proccessMapper($item['items'], $new_path));
            }

          }
        }
      }
    }

    return $new_css_children;

  }

}
