<?php

namespace Drupal\cohesion_custom_styles\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\EntityHasResourceObjectTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Utility\Error;

/**
 * Defines the DX8 Custom Styles entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_custom_style",
 *   label = @Translation("Custom style"),
 *   label_singular = @Translation("Custom style"),
 *   label_plural = @Translation("Custom styles"),
 *   label_collection = @Translation("Custom styles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count custom style",
 *     plural = "@count custom styles",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_custom_styles\CustomStylesListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_custom_styles\Form\CustomStylesForm",
 *       "edit" = "Drupal\cohesion_custom_styles\Form\CustomStylesForm",
 *       "duplicate" = "Drupal\cohesion_custom_styles\Form\CustomStylesForm",
 *       "extend" = "Drupal\cohesion_custom_styles\Form\CustomStylesForm",
 *       "delete" = "Drupal\cohesion_custom_styles\Form\CustomStylesDeleteForm",
 *       "disable" = "Drupal\cohesion_custom_styles\Form\CustomStylesDisableForm",
 *       "enable" = "Drupal\cohesion_custom_styles\Form\CustomStylesEnableForm",
 *       "enable-selection" = "Drupal\cohesion\Form\CohesionEnableSelectionForm",
 *       "disable-selection" = "Drupal\cohesion_custom_styles\Form\CustomStylesDisableSelectionForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion_custom_styles\CustomStylesHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_custom_style",
 *   admin_permission = "administer custom styles",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "class" = "class_name",
 *     "uuid" = "uuid",
 *     "bundle" = "custom_style_type",
 *     "status" = "status",
 *     "selectable" = "selectable"
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/edit",
 *     "add-form" = "/admin/cohesion/styles/cohesion_custom_styles/add/{custom_style_type}",
 *     "delete-form" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/delete",
 *     "add-page" = "/admin/cohesion/styles/cohesion_custom_styles/add",
 *     "collection" = "/admin/cohesion/styles/cohesion_custom_styles",
 *     "extend-form" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/extend",
 *     "duplicate-form" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/duplicate",
 *     "disable" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/disable",
 *     "enable" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/enable",
 *     "enable-selection" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/enable-selection",
 *     "disable-selection" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/disable-selection",
 *     "in-use" = "/admin/cohesion/styles/cohesion_custom_styles/{cohesion_custom_style}/in_use",
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
 *     "class_name",
 *     "custom_style_type",
 *     "available_in_wysiwyg",
 *     "available_in_wysiwyg_inline",
 *     "parent",
 *     "weight"
 *   }
 * )
 */
class CustomStyle extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  use EntityHasResourceObjectTrait {
    getResourceObject as protected getResourceObjectDefault;
  }

  const ASSET_GROUP_ID = 'custom_styles';

  /**
   * The CustomStyleType.
   *
   * @var string
   */
  protected $custom_style_type;

  /**
   * The className.
   *
   * @var string
   */
  protected $class_name;

  /**
   * Id of the entity this was extended from.
   *
   * @var string
   */
  protected $parent;

  /**
   * Custom style weight.
   *
   * @var int
   */
  protected $weight;

  /**
   * Class_name getter.
   *
   * @return string
   */
  public function getClass() {
    return $this->class_name;
  }

  /**
   * Custom_style_type getter.
   *
   * @return string
   */
  public function getCustomStyleType() {
    return $this->custom_style_type;
  }

  /**
   * Getter.
   *
   * @return string
   */
  public function getParent() {
    return $this->parent ?? '';
  }

  /**
   * Getter (look up parent by classname and return entity ID).
   *
   * @return mixed
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function getParentId() {
    try {
      $storage = \Drupal::entityTypeManager()->getStorage('cohesion_custom_style');
    }
    catch (\Throwable $e) {
      return FALSE;
    }

    $ids = $storage->getQuery()
      ->accessCheck(TRUE)
      ->condition('class_name', $this->getParent())
      ->execute();
    if (!empty($ids)) {
      return reset($ids);
    }
    else {
      return FALSE;
    }
  }

  /**
   * Setter.
   *
   * @return \Drupal\cohesion_custom_styles\Entity\CustomStyle
   */
  public function setParent($parent) {
    $this->parent = $parent;
    return $this;
  }

  /**
   * @return array of child entities
   */
  public function getChildEntities() {

    $entities = [];

    try {
      $storage = \Drupal::entityTypeManager()->getStorage('cohesion_custom_style');
      // Usually unable to find the config entity type.
    }
    catch (\Exception $e) {
      return $entities;
    }

    // If this is a parent item, attempt to get the child entities.
    if (!$this->getParent()) {
      $ids = $storage->getQuery()
        ->condition('parent', $this->getClass())
        ->accessCheck(TRUE)
        ->execute();
      $entities = $storage->loadMultiple($ids);
    }

    return $entities;
  }

  /**
   * Custom_style_type weight getter.
   *
   * @return int
   */
  public function getWeight() {
    return $this->weight ?: 0;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if (!$this->getStatus()) {
      $this->set('available_in_wysiwyg', FALSE);
      $this->set('available_in_wysiwyg_inline', FALSE);
    }
  }

  /**
   * @inheritDoc
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('custom_styles_api');
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function process() {
    parent::process();

    /** @var \Drupal\cohesion_custom_styles\Plugin\Api\CustomStylesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();

    $send_to_api->setEntity($this);
    $send_to_api->send();
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function jsonValuesErrors() {
    /** @var \Drupal\cohesion\Plugin\Api\PreviewApi $send_to_api */
    $send_to_api = $this->apiProcessorManager()->createInstance('preview_api');

    $send_to_api->setupPreview($this->getEntityTypeId(), $this->getDecodedJsonValues(), $this->getDecodedJsonMapper());
    $success = $send_to_api->sendWithoutSave();
    $responseData = $send_to_api->getData();

    if ($success === TRUE) {
      return FALSE;
    }
    else {
      return $responseData;
    }
  }

  /**
   * Send the entity to the Api (create/update)
   * {@inheritdoc}.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Send entity to the API to compilation.
    $this->process();

    // Child entities of custom styles also need to be re-saved and sent to the
    // API for compilation.
    $children = $this->getChildEntities();
    if ($children) {
      // Update child entities when parent is updated.
      foreach ($children as $child) {
        // Disable only enabled child.
        if (!$this->status()) {
          if ($child->status()) {
            $child->disable();
            $child->save();
          }
        }
        // @todo Set child entities' parent to this classname.
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getResourceObject() {
    $entity_values = $this->getResourceObjectDefault();

    $entity_values->custom_style_type = $this->get('custom_style_type');
    $entity_values->class_name = $this->get('class_name');

    // Patch in the settings.extended=true for child styles.
    if ($this->getParentId()) {
      if (isset($entity_values->values->styles->settings->extended)) {
        $entity_values->values->styles->settings->extended = TRUE;
      }
    }

    return $entity_values;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    parent::setDefaultValues();

    $this->set('custom_style_type', '');
    $this->set('class_name', '');
    $this->set('available_in_wysiwyg', FALSE);
    $this->set('available_in_wysiwyg_inline', FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    $cohesion_sync_lock = &drupal_static('cohesion_sync_lock');

    // Don't delete child styles if we're importing.
    if (!$cohesion_sync_lock) {
      foreach ($entities as $entity) {
        // Check to see if this entity has any children.
        $children = $entity->getChildEntities();
        if ($children) {
          // Delete all child entities.
          foreach ($children as $child) {
            $child->delete();
          }
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This <em>Custom style</em> has been tracked as in use in the places listed below.<br/><br/>
        <b>Warning:</b> If your style has extended styles you should check these are not in use.
         This style may also be used in other places if you have manually used its class.
         You should not delete this Custom style unless you are sure that it’s not being used.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    if ($this->getParentId()) {
      // Get the parent entity.
      try {
        $parent_entity = \Drupal::entityTypeManager()->getStorage('cohesion_custom_style')->load($this->getParentId());
        // And save it.
        if ($parent_entity && $parent_entity->status()) {
          $parent_entity->save();
        }
      }
      catch (\Exception $e) {
        Error::logException($e, new \Exception(t('Could not load parent custom style')));
      }
    }
    // Add top level deleted entities to a queue to send to the Api on
    // prepareStyleSheet().
    else {
      /** @var \Drupal\cohesion_custom_styles\Plugin\Api\CustomStylesApi $send_to_api */
      $send_to_api = $this->apiProcessorManager()->createInstance('custom_styles_api');

      $send_to_api->setEntity($this);
      $send_to_api->delete();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function createDuplicate() {
    $duplicate = parent::createDuplicate();
    $duplicate->set('class_name', '');
    return $duplicate;
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

  /**
   * Load all custom styles ordered by parent/children and weight.
   *
   * @return array|CustomStyle[]
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public static function loadParentChildrenOrdered() {
    $ids = [];

    $entity_type_repository = \Drupal::service('entity_type.repository');
    $entity_type_manager = \Drupal::entityTypeManager();
    $storage = $entity_type_manager->getStorage($entity_type_repository->getEntityTypeFromClass(get_called_class()));

    $parent_ids = $storage->getQuery()->notExists('parent')
      ->accessCheck(TRUE)
      ->sort('label', 'ASC')
      ->sort('weight', 'ASC')
      ->execute();

    if ($parent_ids) {
      foreach ($parent_ids as $entityId) {
        $ids[$entityId] = $entityId;

        /** @var CustomStyle $parent */
        if ($parent = self::load($entityId)) {
          $children = $storage->getQuery()
            ->accessCheck(TRUE)
            ->condition('parent', $parent->getClass(), '=')
            ->sort('label', 'ASC')
            ->sort('weight', 'ASC')
            ->execute();

          if ($children) {
            $ids += $children;
          }
        }
      }
    }

    return $storage->loadMultiple($ids);
  }

}
