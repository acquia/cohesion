<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\field\Entity\FieldConfig;

/**
 * Defines the Site Studio component entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_component",
 *   label = @Translation("Component"),
 *   label_singular = @Translation("Component"),
 *   label_plural = @Translation("Components"),
 *   label_collection = @Translation("Components"),
 *   label_count = @PluralTranslation(
 *     singular = "@count component",
 *     plural = "@count components",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_elements\ElementsListBuilder",
 *     "form" = {
 *       "default" = "Drupal\cohesion_elements\Form\ComponentForm",
 *       "add" = "Drupal\cohesion_elements\Form\ComponentForm",
 *       "edit" = "Drupal\cohesion_elements\Form\ComponentForm",
 *       "duplicate" = "Drupal\cohesion_elements\Form\ComponentForm",
 *       "delete" = "Drupal\cohesion_elements\Form\ComponentDeleteForm",
 *       "enable-selection" =
 *   "Drupal\cohesion_elements\Form\ComponentEnableSelectionForm",
 *       "disable-selection" =
 *   "Drupal\cohesion_elements\Form\ComponentDisableSelectionForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_component",
 *   admin_permission = "administer components",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "selectable" = "selectable",
 *   },
 *   links = {
 *     "edit-form" =
 *   "/admin/cohesion/components/components/{cohesion_component}/edit",
 *     "add-form" = "/admin/cohesion/components/components/add",
 *     "delete-form" =
 *   "/admin/cohesion/components/components/{cohesion_component}/delete",
 *     "collection" = "/admin/cohesion/components/components",
 *     "duplicate-form" =
 *   "/admin/cohesion/components/components/{cohesion_component}/duplicate",
 *     "in-use" =
 *   "/admin/cohesion/components/components/{cohesion_component}/in_use",
 *     "enable-selection" =
 *   "/admin/cohesion/components/components/{cohesion_component}/enable-selection",
 *     "disable-selection" =
 *   "/admin/cohesion/components/components/{cohesion_component}/disable-selection",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "json_values",
 *     "json_mapper",
 *     "last_entity_update",
 *     "locked",
 *     "modified",
 *     "selectable",
 *     "category",
 *     "preview_image",
 *     "has_quick_edit",
 *     "entity_type_access",
 *     "bundle_access",
 *     "twig_template",
 *     "weight"
 *   }
 * )
 */
class Component extends CohesionElementEntityBase implements CohesionSettingsInterface, CohesionElementSettingsInterface {

  const ASSET_GROUP_ID = 'component';

  const CATEGORY_ENTITY_TYPE_ID = 'cohesion_component_category';

  // When styles are saved for this entity, this is the message.
  const STYLES_UPDATED_SAVE_MESSAGE = 'Your component styles have been updated.';

  const ENTITY_MACHINE_NAME_PREFIX = 'cpt_';

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    // Get the twig filename.
    $filename_prefix = 'component--cohesion-';
    $filename = $filename_prefix . str_replace('_', '-', str_replace('cohesion-helper-', '', $this->get('id')));
    $this->set('twig_template', $filename);

  }

  /**
   * Check whether the content defined where this component has been used is
   * still usable Content may not be usable anymore (and might be lost) if a
   * component field has been remove.
   *
   * @param $json_values
   *   NULL|string - Values to check against if not using the
   *   stored values
   *
   * @return array - the list of entities with broken integrity
   */
  public function checkContentIntegrity($json_values = NULL) {
    $broken_entities = [];
    if ($json_values !== NULL) {
      $this->setJsonValue($json_values);
    }

    $component_field_uuids = [];
    foreach ($this->getLayoutCanvasInstance()
      ->iterateComponentForm() as $form_element) {
      $component_field_uuids[] = $form_element->getUUID();
    }

    $in_use_list = \Drupal::service('cohesion_usage.update_manager')
      ->getInUseEntitiesList($this);

    foreach ($in_use_list as $uuid => $type) {

      $entity = \Drupal::service('entity.repository')
        ->loadEntityByUuid($type, $uuid);

      if ($entity instanceof ContentEntityInterface) {
        foreach ($entity->getFieldDefinitions() as $field) {
          if ($field instanceof FieldConfig) {
            // cohesion_layout reference.
            if ($field->getSetting('handler') == 'default:cohesion_layout') {
              /** @var \Drupal\Core\Entity\ContentEntityInterface $item */
              foreach ($entity->get($field->getName()) as $item) {
                try {
                  $target_id = $item->getValue()['target_id'];
                  if (!is_null($item->getValue()['target_id'])) {
                    /** @var \Drupal\cohesion_elements\Entity\CohesionLayout $cohesion_layout_entity */
                    $cohesion_layout_entity = \Drupal::service('entity_type.manager')
                      ->getStorage('cohesion_layout')
                      ->load($target_id);
                    if (!$this->hasDefinedContentForRemovedComponentField($cohesion_layout_entity, $component_field_uuids)) {
                      $broken_entities[$entity->uuid()] = $entity;
                    }
                  }
                }
                catch (\Exception $e) {
                  break;
                }
              }
            }
          }
        }
      }
      elseif (!$this->hasDefinedContentForRemovedComponentField($entity, $component_field_uuids)) {
        $broken_entities[$entity->uuid()] = $entity;
      }
    }

    return array_values($broken_entities);
  }

  /**
   * Check whether an entity using this component has content defined for a field that no
   * long exists in the component form.
   *
   * @param $entity
   *   \Drupal\Core\Entity\EntityInterface - the entity using this component
   * @param $component_field_uuids
   *   array - the list of field's uuid for this component
   *
   * @return bool
   */
  private function hasDefinedContentForRemovedComponentField($entity, $component_field_uuids) {
    if ($entity instanceof EntityJsonValuesInterface && $entity->isLayoutCanvas()) {
      $canvas = $entity->getLayoutCanvasInstance();
      foreach ($canvas->iterateCanvas() as $canvas_element) {
        $element_model = $canvas_element->getModel();
        if ($canvas_element->getProperty('componentId') == $this->id()) {
          foreach ($element_model->getValues() as $model_key => $model_value) {
            if (preg_match(ElementModel::MATCH_UUID, $model_key) && !in_array($model_key, $component_field_uuids)) {
              return FALSE;
            }
          }
        }
      }
    }
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    parent::process();
    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $template_api */
    $template_api = $this->getApiPluginInstance();
    $template_api->setEntity($this);
    $template_api->send();

    return $template_api;
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Invalidate the template cache.
    self::clearCache($this);

    $this->process();
  }

  /**
   * Delete the template twig cache (if available) and invalidate the render
   * cache tags.
   */
  protected static function clearCache($entity) {
    // The twig filename for this template.
    $filename = $entity->get('twig_template');

    _cohesion_templates_delete_twig_cache_file($filename);

    // Content template.
    $entity_cache_tags = ['component.cohesion.' . $entity->id()];

    // Invalidate render cache tag for this template.
    \Drupal::service('cache_tags.invalidator')
      ->invalidateTags($entity_cache_tags);

    // And clear the theme cache.
    parent::clearCache($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This Component has been tracked as in use in the places listed below. You should not delete it until you have removed its use.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    // Remove preview images and component content- update usage and delete file if necessary.
    foreach ($entities as $entity) {
      // Delete any component contents for this component.
      $storage = \Drupal::entityTypeManager()->getStorage('component_content');
      $query = $storage->getQuery()->condition('component', $entity->id());

      $ids = $query->execute();
      $entities = $storage->loadMultiple($ids);
      $storage->delete($entities);

      // Clear the cache for this component.
      self::clearCache($entity);
    }
  }

  /**
   * Return the URI of the twig template for this component.
   *
   * @return bool|string
   */
  protected function getTwigPath() {
    return $this->get('twig_template') ? COHESION_TEMPLATE_PATH . '/' . $this->get('twig_template') . '.html.twig' : FALSE;
  }

  /**
   *
   */
  public function getTwigFilename($theme_name = NULL) {
    if ($this->get('twig_template')) {
      if (!is_null($theme_name)) {
        return $this->get('twig_template') . '--' . str_replace('_', '-', $theme_name);
      }
      return $this->get('twig_template');
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    // Clear the global template entry if it exists
    $theme_filename = $this->getTwigFilename() . '.html.twig';
    \Drupal::service('cohesion.template_storage')->delete($theme_filename);

    // Clear any theme specific themes
    foreach (\Drupal::service('cohesion.utils')->getCohesionEnabledThemes() as $theme_info) {
      $theme_filename = $this->getTwigFilename($theme_info->getName()) . '.html.twig';
      \Drupal::service('cohesion.template_storage')->delete($theme_filename);
    }
  }

}
