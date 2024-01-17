<?php

namespace Drupal\cohesion_style_guide\Services;

use Drupal\cohesion\Services\CohesionUtils;
use Drupal\cohesion\UsageUpdateManager;
use Drupal\cohesion_style_guide\Entity\StyleGuideManager;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\Extension;
use Drupal\Core\Extension\ThemeHandlerInterface;

/**
 * The style guide manager handler service.
 *
 * @package Drupal\cohesion_style_guide
 */
class StyleGuideManagerHandler {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * @var \Drupal\cohesion\UsageUpdateManager
   */
  protected $usageUpdateManager;

  /**
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected $themeHandler;

  /**
   * The cohesion utils helper.
   *
   * @var \Drupal\cohesion\Services\CohesionUtils
   */
  protected $cohesionUtils;

  /**
   * StyleGuideManagerHandler constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   * @param \Drupal\cohesion\UsageUpdateManager $usage_update_manager
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   * @param \Drupal\cohesion\Services\CohesionUtils $cohesion_utils
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository, UsageUpdateManager $usage_update_manager, ThemeHandlerInterface $theme_handler, CohesionUtils $cohesion_utils) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
    $this->usageUpdateManager = $usage_update_manager;
    $this->themeHandler = $theme_handler;
    $this->cohesionUtils = $cohesion_utils;
  }

  /**
   * Get the json values for the style guide manager for a specific theme.
   *
   * @param $theme_id
   *
   * @return false|string
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getStyleGuideManagerJson($theme_id) {
    $style_guide_managers_ids = $this->entityTypeManager->getStorage('cohesion_style_guide_manager')
      ->getQuery()
      ->accessCheck(TRUE)
      ->condition('theme', $theme_id)
      ->execute();

    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuideManager[] $style_guide_managers */
    $style_guide_managers = StyleGuideManager::loadMultiple($style_guide_managers_ids);

    $model = [];
    $changedFields = [];
    foreach ($style_guide_managers as $style_guide_manager) {
      $style_guide_manager_json_values = $style_guide_manager->getDecodedJsonValues(TRUE);
      if (property_exists($style_guide_manager_json_values, 'model')) {
        foreach ($style_guide_manager_json_values->model as $uuid => $model_value) {
          $model[$uuid] = $model_value;
        }
      }
      if (property_exists($style_guide_manager_json_values, 'changedFields')) {
        $changedFields = array_merge($changedFields, $style_guide_manager_json_values->changedFields);
      }
    }

    return empty($model) ? '{}' : json_encode([
      'model' => $model,
      'changedFields' => $changedFields,
    ]);

  }

  /**
   * Get the merged parent style guide manager model with the theme values also
   * merged with its parent(s) values.
   *
   * @param $theme_id
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getStyleGuideManagerJsonWithParentMerged($theme_id) {
    $parent_json = NULL;
    $themes = $this->themeHandler->listInfo();

    // Check that we have some themes and they are an array.
    if (isset($theme_id, $themes) && is_array($themes)) {
      // Check that the theme has a "base theme" as this can be set to "false".
      if ($themes[$theme_id] instanceof Extension && $baseThemes = $themes[$theme_id]->base_themes) {
        // Loop over each parent value from to parent to the direct parent of
        // the theme, and merge them all together by overriding the value if the
        // field being looked at is in the list of changedFields.
        if (is_array($baseThemes)) {
          foreach ($baseThemes as $base_theme_id => $base_theme_name) {
            $child_json = json_decode($this->getStyleGuideManagerJson($base_theme_id));
            $parent_json = $this->mergeChildJson($parent_json, $child_json);
          }
        }

        if (property_exists($parent_json, 'changedFields')) {
          unset($parent_json->changedFields);
        }
      }
    }
    $parent_values = empty($parent_json) ? '{}' : json_encode($parent_json);

    // Then merge the desired theme into the merged parents model.
    $theme_json = json_decode($this->getStyleGuideManagerJson($theme_id));

    $theme_model = $this->mergeChildJson($parent_json, $theme_json);

    $theme_values = empty($theme_model) ? '{}' : json_encode($theme_model);

    return ['theme' => $theme_values, 'parent' => $parent_values];
  }

  /**
   * Merge a child style guide manager json into a parent model.
   *
   * @param $current_json
   * @param $child_json
   *
   * @return mixed
   */
  public function mergeChildJson($current_json, $child_json) {
    if (is_null($current_json) || !property_exists($current_json, 'model')) {
      $current_json = $child_json;
    }
    elseif (property_exists($child_json, 'model') && is_object($child_json->model)) {
      if (property_exists($child_json, 'changedFields')) {
        $current_json->changedFields = $child_json->changedFields;
      }
      foreach ($child_json->model as $style_guide_uuid => $model_values) {
        // Add the style guide to the model if it does not exists yet
        // usually happens at the top parent level.
        if (!property_exists($current_json->model, $style_guide_uuid)) {
          $current_json->model->{$style_guide_uuid} = $model_values;
        }

        if (is_object($model_values)) {
          foreach ($model_values as $value_uuid => $value) {
            // If the value does not exist add it regardless of its content
            // or if the field has been set to override its parent value.
            if (!property_exists($current_json->model->{$style_guide_uuid}, $value_uuid) ||
              (property_exists($child_json, 'changedFields') && is_array($child_json->changedFields) && in_array("model.{$style_guide_uuid}.{$value_uuid}", $child_json->changedFields))) {
              $current_json->model->{$style_guide_uuid}->{$value_uuid} = $value;
            }
          }
        }
      }
    }

    return $current_json;
  }

  /**
   * Build the style guide manager json definition for the frontend to consume.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getJsonDefinition() {
    $style_guide_manager_form = [];
    /** @var \Drupal\cohesion_style_guide\Entity\StyleGuide[] $style_guides */
    $style_guide_storage = $this->entityTypeManager
      ->getStorage('cohesion_style_guide');

    $style_guide_ids = $style_guide_storage->getQuery()
      ->accessCheck(TRUE)
      ->sort('weight')
      ->condition('status', TRUE)
      ->execute();
    $style_guides = $style_guide_storage->loadMultiple($style_guide_ids);
    foreach ($style_guides as $style_guide) {
      // Add enabled style guides to style guide manager.
      if ($style_guide->status()) {
        $style_guide_manager_form[] = [
          'label' => $style_guide->label(),
          'uuid' => $style_guide->uuid(),
          'json_values' => $style_guide->getDecodedJsonValues(TRUE),
        ];
      }
    }

    return $style_guide_manager_form;
  }

  /**
   * Save instances of StyleGuideManager and return the list of entities that
   * needs to be rebuilt.
   *
   * @param $theme_id
   * @param $json_values
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function saveStyleGuideManager($theme_id, $json_values) {

    $in_use_entities = [];
    if (isset($this->themeHandler->listInfo()[$theme_id])) {
      $theme = $this->themeHandler->listInfo()[$theme_id];

      $style_guide_managers_ids = $this->entityTypeManager->getStorage('cohesion_style_guide_manager')
        ->getQuery()
        ->accessCheck(TRUE)
        ->condition('theme', $theme_id)
        ->execute();

      /** @var \Drupal\cohesion_style_guide\Entity\StyleGuideManager[] $style_guide_managers */
      $style_guide_managers = StyleGuideManager::loadMultiple($style_guide_managers_ids);
      $decoded_json = json_decode($json_values);

      if (property_exists($decoded_json, 'model') && is_object($decoded_json->model)) {
        foreach ($decoded_json->model as $style_guide_uuid => $style_guide_values) {

          if ($style_guide = $this->entityRepository
            ->loadEntityByUuid('cohesion_style_guide', $style_guide_uuid)) {
            // Add the list of entities that will need to be re-saved.
            $in_use_entities = array_merge($in_use_entities, $this->usageUpdateManager->getInUseEntitiesList($style_guide));
            $style_guide_manager_id = $style_guide_uuid . $theme_id;

            // Loop through each field value and check if the value has been
            // changed (compared to its parent) and add remove it if not.
            $style_guide_changed_fields = [];
            if (is_object($style_guide_values)) {
              foreach ($style_guide_values as $field_uuid => $value) {
                if (property_exists($decoded_json, 'changedFields') && is_array($decoded_json->changedFields) && in_array("model.{$style_guide_uuid}.{$field_uuid}", $decoded_json->changedFields)) {
                  $style_guide_changed_fields[] = "model.{$style_guide_uuid}.{$field_uuid}";
                }
                else {
                  unset($style_guide_values->{$field_uuid});
                }
              }
            }

            // Only save or update a style guide if is has changed values.
            if (!empty($style_guide_changed_fields)) {

              // Build the json for the style guide manager.
              $style_guide_manager_values = [
                'model' => [
                  $style_guide_uuid => $style_guide_values,
                ],
                'changedFields' => $style_guide_changed_fields,
              ];

              $style_json_values = json_encode($style_guide_manager_values);

              // Style guide manager instance already exists -> update.
              if (isset($style_guide_managers[$style_guide_manager_id])) {
                $style_guide_manager = $style_guide_managers[$style_guide_manager_id];
                // Unset it after it's been save so any style guide manager
                // instance left are to be deleted.
                unset($style_guide_managers[$style_guide_manager_id]);

                // Do not update if nothing has changed.
                if ($style_guide_manager->getDecodedJsonValues(TRUE) == json_decode($style_json_values)) {
                  continue;
                }

              }
              else {
                // Style guide manager instance does not exists -> create new
                // one.
                /** @var \Drupal\cohesion_style_guide\Entity\StyleGuideManager $style_guide_manager */
                $style_guide_manager = StyleGuideManager::create([
                  'id' => $style_guide_uuid . $theme_id,
                  'label' => $style_guide->label() . ' ' . $theme->info['name'],
                  'theme' => $theme_id,
                  'style_guide_uuid' => $style_guide_uuid,
                ]);
              }

              // Set the json values to the style guide manager instance and
              // save.
              $style_guide_manager->setJsonValue($style_json_values);
              $style_guide_manager->save();

              $in_use_entities = array_merge($in_use_entities, $this->usageUpdateManager->getInUseEntitiesList($style_guide));
            }
          }

        }
        // Delete any remaining style guide manager instance.
        foreach ($style_guide_managers as $style_guide_manager) {
          $style_guide_manager->delete();
        }
      }
    }

    return $in_use_entities;
  }

  /**
   * @param $theme_info
   *
   * @return array
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getTokenValues($theme_info) {

    // Is there a style guide manager preview.
    $preview_sgm_values = &drupal_static('coh_preview_tokens_sgm_' . $theme_info->getName());
    if ($preview_sgm_values) {
      $json_values = $preview_sgm_values;
    }
    else {
      $json_values = $this->getStyleGuideManagerJson($theme_info->getName());
    }

    $style_guide_tokens = [];
    if (!is_null($json_values)) {
      // Decode the theme style guide manager to retrieve values.
      $style_guide_manager = json_decode($json_values, TRUE);

      // Load all style enabled guides and extract token names.
      /** @var \Drupal\cohesion_style_guide\Entity\StyleGuide[] $style_guides */
      $style_guide_storage = \Drupal::entityTypeManager()
        ->getStorage('cohesion_style_guide');
      $style_guide_ids = $style_guide_storage->getQuery()
        ->accessCheck(TRUE)
        ->sort('weight')
        ->condition('status', TRUE)
        ->execute();
      $style_guides = $style_guide_storage->loadMultiple($style_guide_ids);

      foreach ($style_guides as $style_guide) {
        if (isset($style_guide_manager['model'][$style_guide->uuid()])) {

          $style_guide_manager_model = $style_guide_manager['model'][$style_guide->uuid()];

          $layout_canvas = $style_guide->getLayoutCanvasInstance();
          foreach ($layout_canvas->iterateStyleGuideForm() as $form_element) {

            $token_name = $form_element->getModel()->getProperty([
              'settings',
              'machineName',
            ]);

            $token_uuid = $form_element->getModel()->getUUID();
            if (isset($style_guide_manager_model[$token_uuid])) {
              $token = "{$style_guide->id()}:{$token_name}";

              $style_guide_tokens[$token] = $this->cohesionUtils->processFieldValues($style_guide_manager_model[$token_uuid], $form_element->getModel());
            }
          }
        }
      }
    }

    return $style_guide_tokens;
  }

  /**
   * Return a list of all style guide fields with whether they can be previewed.
   *
   * @return array
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function tokensCanBePreview() {
    $style_guide_manager_json_values = $this->getJsonDefinition();

    $preview_model = [];

    foreach ($style_guide_manager_json_values as $style_guide_values) {
      $style_guide_uuid = $style_guide_values['uuid'];
      /** @var \Drupal\cohesion_style_guide\Entity\StyleGuide $style_guide */
      if ($style_guide = $this->entityRepository
        ->loadEntityByUuid('cohesion_style_guide', $style_guide_uuid)) {

        $usage = $this->usageUpdateManager->getInUseEntitiesList($style_guide);
        // Set each field of the style guide to can be previewed by default.
        if (property_exists($style_guide_values['json_values'], 'model')) {
          foreach ($style_guide_values['json_values']->model as $style_guide_value_uuid => $style_guide_value) {
            $preview_model[$style_guide_value_uuid] = FALSE;
          }
        }

        // Loop through all in use entities.
        foreach ($usage as $source_uuid => $source_type) {
          // Only base style and custom style can be previewed.
          if ($source_type == 'cohesion_custom_style' || $source_type == 'cohesion_base_styles') {
            /** @var \Drupal\cohesion\Entity\CohesionConfigEntityBase $entity */
            $entity = $this->entityRepository->loadEntityByUuid($source_type, $source_uuid);

            if ($entity instanceof EntityInterface && $instance = $this->usageUpdateManager->getPluginInstanceForEntity($entity)) {
              // Get the scannable data (usually JSON for entities).
              $scannable_data = $instance->getScannableData($entity);

              foreach ($scannable_data as $scannable) {
                if (in_array($scannable['type'], ['json_string', 'string']) && property_exists($style_guide_values['json_values'], 'model')) {
                  // Loop through each field of the style guide.
                  foreach ($style_guide_values['json_values']->model as $style_guide_value_uuid => $style_guide_value) {
                    // If the style guide field has not yet be marked has
                    // previewable && has a machine name check if there is at
                    // least one occurrence of it's token in the entity.
                    if ($preview_model[$style_guide_value_uuid] == FALSE && property_exists($style_guide_value, 'settings') && property_exists($style_guide_value->settings, 'machineName') &&
                            strpos($scannable['value'], "[style-guide:{$style_guide->id()}:{$style_guide_value->settings->machineName}]") > 0) {
                      $preview_model[$style_guide_value_uuid] = TRUE;
                    }
                  }
                }
              }
            }
          }
        }
      }
    }

    return $preview_model;
  }

}
