<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update component forms to use machine names.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0024",
 * )
 */
class _0024EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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
    // A list of the already used machine names, so there are no duplicates.
    $machine_names = [];

    if ($entity instanceof EntityJsonValuesInterface) {
      $json_values = $entity->getDecodedJsonValues(TRUE);

      // Only run if this has a component form model.
      if ($entity->isLayoutCanvas() && property_exists($json_values, 'componentForm')) {
        // Remove all humanId keys from componentForm fields.
        $layoutCanvas = $entity->getLayoutCanvasInstance();

        foreach ($layoutCanvas->iterateComponentForm() as $element) {
          $element->unsetProperty('humanId');
        }

        foreach ($layoutCanvas->iterateComponentForm() as $element) {
          $this->assignMachineName($element, $machine_names);
        }

        $entity->setJsonValue(json_encode($layoutCanvas));

        // Build up meta.fieldHistory object for every component.
        // An array per form field containing it’s type
        // (componentForm[index].uid) and uuid (componentForm[index].uuid) as
        // well as the new machineName generated above.
        $json_values = $entity->getDecodedJsonValues(TRUE);
        $json_values->meta = new \stdClass();
        $json_values->meta->fieldHistory = [];

        $machine_names = array_flip($machine_names);

        foreach ($layoutCanvas->iterateComponentForm() as $componentFormItem) {
          if (isset($machine_names[$componentFormItem->getproperty('uuid')])) {
            // Build the history object up.
            $item = new \stdClass();
            $item->uuid = $componentFormItem->getproperty('uuid');
            $item->type = $componentFormItem->getproperty('uid');
            $item->machineName = $machine_names[$componentFormItem->getproperty('uuid')];
            $json_values->meta->fieldHistory[] = $item;
          }
        }
        $entity->setJsonValue(json_encode($json_values));

      }

    }

    return TRUE;
  }

  /**
   * @param \Drupal\cohesion\LayoutCanvas\Element $element
   * @param $machine_names
   */
  private function assignMachineName(&$element, &$machine_names) {

    if ($element->getproperty('type') === 'form-field') {
      $model = $element->getModel();

      if ($model && $title = $model->getProperty(['settings', 'title'])) {
        // Build the base machine name.
        $machine_name = $this->machineNameFromTitle($title);

        // Make sure it's unique.
        $base_machine_name = $machine_name;

        $i = 2;
        while (isset($machine_names[$machine_name])) {
          $machine_name = $base_machine_name . $i++;
        }

        // Add it to the list of all machine names.
        $machine_names[$machine_name] = $model->getUUID();

        $model->setProperty(['settings', 'machineName'], $machine_name);
      }
    }
  }

  /**
   * @param $machine_name
   *
   * @return mixed|string|string[]|null
   */
  public function machineNameFromTitle($machine_name) {
    $machine_name = strtolower($machine_name);
    $machine_name = trim(preg_replace("/[^a-z0-9 ]/", '', $machine_name));
    $machine_name = str_replace(' ', '-', $machine_name);

    if (empty($machine_name)) {
      $machine_name = 'field';
    }

    return $machine_name;
  }

}
