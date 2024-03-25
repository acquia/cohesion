<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_base_styles\Entity\BaseStyles;
use Drupal\cohesion_custom_styles\Entity\CustomStyle;
use Drupal\cohesion_style_helpers\Entity\StyleHelper;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update link animations and modifiers.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0018",
 * )
 */
class _0018EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof CustomStyle || $entity instanceof BaseStyles || $entity instanceof StyleHelper) {

      $mapper = $entity->getDecodedJsonMapper();

      $this->recurseMapper($mapper);

      $entity->setJsonMapper(json_encode($mapper));
    }

    return TRUE;
  }

  /**
   *
   */
  public function recurseMapper(&$mapper) {
    if (is_object($mapper) || is_array($mapper)) {

      if (is_object($mapper) && property_exists($mapper, 'form') && property_exists($mapper, 'formDefinition')) {
        $mapper->form = NULL;
      }

      foreach ($mapper as &$child) {
        $this->recurseMapper($child);
      }

    }

  }

}
