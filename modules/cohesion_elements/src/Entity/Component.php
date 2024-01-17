<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\Entity\ContentIntegrityInterface;
use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\LayoutCanvas\ElementModel;
use Drupal\cohesion\TemplateEntityTrait;
use Drupal\cohesion\TemplateStorage\TemplateStorageBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
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
 *     "list_builder" = "Drupal\cohesion_elements\ComponentListBuilder",
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
class Component extends CohesionElementEntityBase implements CohesionSettingsInterface, CohesionElementSettingsInterface, ContentIntegrityInterface {

  use TemplateEntityTrait;

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
    $filename_prefix = 'component' . TemplateStorageBase::TEMPLATE_PREFIX;
    $filename = $filename_prefix . str_replace('_', '-', str_replace('cohesion-helper-', '', $this->get('id')));
    $this->set('twig_template', $filename);

  }

  /**
   * {@inheritdoc}
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

    // Get the of entities where this component is being used
    $in_use_list = \Drupal::service('cohesion_usage.update_manager')
      ->getInUseEntitiesList($this);

    foreach ($in_use_list as $uuid => $type) {

      $entity = \Drupal::service('entity.repository')
        ->loadEntityByUuid($type, $uuid);

      // If content entity find the layout canvas entity
      if ($entity instanceof ContentEntityInterface) {
        foreach ($entity->getFieldDefinitions() as $field) {
          if ($field instanceof FieldConfig && $field->getSetting('handler') == 'default:cohesion_layout') {
            foreach ($entity->get($field->getName()) as $item) {
              if ($target_id = $item->getValue()['target_id']) {
                $cohesion_layout_entity = CohesionLayout::load($target_id);
                if ($cohesion_layout_entity && !$this->hasDefinedContentForRemovedComponentField($cohesion_layout_entity, $component_field_uuids)) {
                  $broken_entities[$entity->uuid()] = $entity;
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
   * Check whether an entity using this component has content defined for a
   * field that no long exists in the component form.
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
        if (!$canvas_element->getComponentContentId() && $element_model && $canvas_element->getProperty('componentId') == $this->id()) {
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

    // Remove preview images and component content- update usage and delete file
    // if necessary.
    foreach ($entities as $entity) {
      // Delete any component contents for this component.
      $storage = \Drupal::entityTypeManager()->getStorage('component_content');
      $query = $storage->getQuery()
        ->accessCheck(FALSE)
        ->condition('component', $entity->id());

      $ids = $query->execute();
      $entities = $storage->loadMultiple($ids);
      $storage->delete($entities);

      // Clear the cache for this component.
      self::clearCache($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {

    $template_api = $this->getApiPluginInstance();
    $template_api->setEntity($this);
    $template_api->delete();

    parent::delete();
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    $this->removeAllTemplates();
  }

}
