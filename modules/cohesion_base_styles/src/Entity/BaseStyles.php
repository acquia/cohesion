<?php

namespace Drupal\cohesion_base_styles\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\EntityHasResourceObjectTrait;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Site Studio base styles entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_base_styles",
 *   label = @Translation("Base style"),
 *   label_singular = @Translation("Base style"),
 *   label_plural = @Translation("Base styles"),
 *   label_collection = @Translation("Base styles"),
 *   label_count = @PluralTranslation(
 *     singular = "@count base style",
 *     plural = "@count base styles",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_base_styles\BaseStylesListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_base_styles\Form\BaseStylesForm",
 *       "edit" = "Drupal\cohesion_base_styles\Form\BaseStylesForm",
 *       "duplicate" = "Drupal\cohesion_base_styles\Form\BaseStylesForm",
 *       "delete" = "Drupal\cohesion_base_styles\Form\BaseStylesDeleteForm",
 *       "enable" = "Drupal\cohesion_base_styles\Form\BaseStylesEnableForm",
 *       "disable" = "Drupal\cohesion_base_styles\Form\BaseStylesDisableForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_base_styles",
 *   admin_permission = "administer base styles",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status"
 *   },
 *   links = {
 *     "canonical" =
 *   "/admin/cohesion/styles/cohesion_base_styles/{cohesion_base_styles}/edit",
 *     "edit-form" =
 *   "/admin/cohesion/styles/cohesion_base_styles/{cohesion_base_styles}/edit",
 *     "add-form" = "/admin/cohesion/styles/cohesion_base_styles/add",
 *     "delete-form" =
 *   "/admin/cohesion/styles/cohesion_base_styles/{cohesion_base_styles}/delete",
 *     "duplicate-form" =
 *   "/admin/cohesion/styles/cohesion_base_styles/{cohesion_base_styles}/duplicate",
 *     "collection" = "/admin/cohesion/styles/cohesion_base_styles",
 *     "disable" =
 *   "/admin/cohesion/styles/cohesion_base_styles/{cohesion_base_styles}/disable",
 *     "enable" =
 *   "/admin/cohesion/styles/cohesion_base_styles/{cohesion_base_styles}/enable",
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
 *     "custom"
 *   }
 * )
 */
class BaseStyles extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  use EntityHasResourceObjectTrait;

  const ASSET_GROUP_ID = 'base_styles';

  const ENTITY_MACHINE_NAME_PREFIX = 'base_';

  /**
   * @inheritDoc
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('base_styles_api');
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    parent::process();

    // Only send to the API if the base style is enabled.
    if ($this->status) {
      /** @var \Drupal\cohesion_base_styles\Plugin\Api\BaseStylesApi $send_to_api */
      $send_to_api = $this->getApiPluginInstance();
      $send_to_api->setEntity($this);
      $send_to_api->send();
    }
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);
    if ($this->status()) {
      $this->process();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    /** @var \Drupal\cohesion_base_styles\Plugin\Api\BaseStylesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $send_to_api->delete();
  }

  /**
   * Import a list of entities.
   *
   * Add new entities at first import.
   *
   * @param array $entities
   *   Array of entities to import.
   *
   * @return bool
   *   Return TRUE if entities were imported, otherwise FALSE.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   *   In case of failures an exception is thrown.
   */
  public static function importEntities(array $entities) {
    // Check if we have any entities to import.
    $current_entities = self::loadMultiple();
    if (empty($current_entities)) {

      if (!is_array($entities) || (count($entities) == 0)) {
        return FALSE;
      }

      // Import each entity.
      $canonical_list = [];
      foreach ($entities as $e) {
        // Try to import entity.
        if ($e['element_id'] !== 'generic') {
          $entity = self::create([
            'id' => $e['element_id'],
            'label' => $e['element_label'],
          ]);
          $entity->setDefaultValues();
          $entity->save();
        }

        $canonical_list[$e['element_id']] = $e['element_label'];
      }
    }

    return TRUE;
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

}
