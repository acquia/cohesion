<?php

namespace Drupal\cohesion\Plugin\EntityUpdate;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityUpdatePluginInterface;
use Drupal\Component\Plugin\PluginBase;

/**
 * Update link animations and modifiers.
 *
 * @package Drupal\cohesion
 *
 * @EntityUpdate(
 *   id = "entityupdate_0012",
 * )
 */
class _0012EntityUpdate extends PluginBase implements EntityUpdatePluginInterface {

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

      $json_values = $entity->getDecodedJsonValues(TRUE);

      if ($entity->isLayoutCanvas()) {
        $layoutCanvas = $entity->getLayoutCanvasInstance();

        foreach ($layoutCanvas->iterateModels('canvas') as $model) {
          $elements = ['link', 'container', 'column', 'button', 'slide'];
          $types = ['animation', 'modifier'];
          if (in_array($model->getElement()->getProperty('uid'), $elements) && in_array($model->getProperty([
            'settings',
            'type',
          ]), $types)) {
            $json_values->model->{$model->getUUID()}->settings->type = 'interaction';
          }

          $mapper_form_keys = [
            'link-settings',
            'container-settings',
            'column-settings',
            'button-settings',
            'slide-settings',
          ];
          $has_settings = $this->mapperHasSettings($json_values, $model->getUUID(), $mapper_form_keys);
          if ($has_settings !== FALSE) {
            $index = $has_settings['index'];
            $formKey = $has_settings['formKey'];
            if (!property_exists($json_values->mapper->{$model->getUUID()}->settings->topLevel->formDefinition[$index], 'children')) {
              $json_values->mapper->{$model->getUUID()}->settings->topLevel->formDefinition[$index]->children = [];
            }
            if (is_array($json_values->mapper->{$model->getUUID()}->settings->topLevel->formDefinition[$index]->children)) {
              $modifier = new \stdClass();
              switch ($formKey) {
                case 'link-settings':
                  $modifier->formKey = 'link-modifier';
                  break;

                case 'button-settings':
                  $modifier->formKey = 'button-modifier';
                  break;

                default:
                  $modifier->formKey = 'common-link-modifier';
                  break;
              }
              $modifier->breakpoints = [];
              $activeFields = new \stdClass();
              $activeFields->name = 'modifierType';
              $activeFields->active = TRUE;
              $modifier->activeFields = [
                $activeFields,
              ];

              $json_values->mapper->{$model->getUUID()}->settings->topLevel->formDefinition[$index]->children[] = $modifier;

              $animation = new \stdClass();
              switch ($formKey) {
                case 'link-settings':
                  $animation->formKey = 'link-animation';
                  break;

                case 'button-settings':
                  $animation->formKey = 'button-animation';
                  break;

                default:
                  $animation->formKey = 'common-link-animation';
                  break;
              }
              $animation->breakpoints = [];
              $breakpoint = new \stdClass();
              $breakpoint->name = 'xl';
              $animation->breakpoints[] = $breakpoint;
              $activeFields = new \stdClass();
              $activeFields->name = 'animationType';
              $activeFields->active = TRUE;
              $animation->activeFields = [
                $activeFields,
              ];

              $has_animation = FALSE;
              foreach ($json_values->mapper->{$model->getUUID()}->settings->topLevel->formDefinition[$index]->children as $mapper) {
                if ($mapper->formKey == $animation->formKey) {
                  $has_animation = TRUE;
                  break;
                }
              }

              if (!$has_animation) {
                $json_values->mapper->{$model->getUUID()}->settings->topLevel->formDefinition[$index]->children[] = $animation;
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
  private function mapperHasSettings($json_values, $uuid, $formKeys) {

    $encode = json_encode($json_values);
    $array_assos = json_decode($encode, TRUE);
    if (isset($array_assos['mapper'][$uuid]['settings']['topLevel']['formDefinition']) && is_array($array_assos['mapper'][$uuid]['settings']['topLevel']['formDefinition'])) {
      foreach ($array_assos['mapper'][$uuid]['settings']['topLevel']['formDefinition'] as $index => $formDefinition) {
        if (isset($formDefinition['formKey'])) {
          foreach ($formKeys as $formKey) {
            if ($formDefinition['formKey'] == $formKey) {
              return ['index' => $index, 'formKey' => $formKey];
            }
          }
        }
      }
    }

    return FALSE;
  }

}
