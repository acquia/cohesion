<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Attribute\EntityUpdate;
use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\cohesion_elements\Entity\ComponentContent;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update Component content references on layout canvases from IDs to UUIDs.
 *
 * @package Drupal\cohesion
 */
#[EntityUpdate(
  id: "entityupdate_0032",
)]
class _0032EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    if ($entity instanceof EntityJsonValuesInterface) {
      if ($entity->isLayoutCanvas()) {
        $layoutCanvas = $entity->getLayoutCanvasInstance();

        foreach ($layoutCanvas->iterateCanvas() as $element) {
          if ($cc_id = $element->getProperty('componentContentId')) {
            $uuid = $this->fetchUUID($cc_id);
            if ($uuid) {
              $element->setProperty('componentContentId', $uuid);
              $element->setProperty('uid', $uuid);
            }
          }
        }

        $entity->setJsonValue(json_encode($layoutCanvas));
      }

    }

    return TRUE;
  }

  /**
   * Fetch the Component content UUID and return in correct format.
   * @param $id
   *
   * @return string|false
   */
  public function fetchUUID($id) {
    $cc_id = str_replace('cc_', '', $id);
    $cc = $this->loadComponentContent($cc_id);
    if ($cc) {
      return 'cc_' . $cc->uuid();
    }
    return FALSE;
  }

  /**
   * Load ComponentContent entity by ID.
   *
   * @param string|int $cc_id
   *   The component content ID.
   *
   * @return \Drupal\cohesion_elements\Entity\ComponentContent|null
   *   The loaded entity or NULL.
   */
  protected function loadComponentContent($cc_id) {
    return ComponentContent::load($cc_id);
  }

}
