<?php

namespace Drupal\cohesion_website_settings\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\EntityHasResourceObjectTrait;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Defines the Site Studio website settings entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_website_settings",
 *   label = @Translation("Website settings"),
 *   label_singular = @Translation("Website setting"),
 *   label_plural = @Translation("Website settings"),
 *   label_collection = @Translation("Website settings"),
 *   label_count = @PluralTranslation(
 *     singular = "@count website setting",
 *     plural = "@count website settings",
 *   ),
 *   fieldable = TRUE,
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_website_settings\WebsiteSettingsListBuilder",
 *     "form" = {
 *       "edit" = "Drupal\cohesion_website_settings\Form\WebsiteSettingsForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_website_settings",
 *   admin_permission = "administer website settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "edit-form" =
 *   "/admin/cohesion/cohesion_website_settings/{cohesion_website_settings}/edit",
 *     "collection" = "/admin/cohesion/cohesion_website_settings"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "json_values",
 *     "json_mapper",
 *     "last_entity_update",
 *     "locked",
 *     "modified",
 *     "selectable"
 *   }
 * )
 */
class WebsiteSettings extends WebsiteSettingsEntityBase implements CohesionSettingsInterface {

  use EntityHasResourceObjectTrait;
  use StringTranslationTrait;

  const ASSET_GROUP_ID = 'website_settings';

  /**
   * The human-readable label for a collection of entities of the type.
   *
   * @var string
   *
   * @see \Drupal\Core\Entity\EntityTypeInterface::getCollectionLabel()
   */
  protected $label_collection = '';

  /**
   * {@inheritdoc}
   */
  public function process() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);

    // Send or delete.
    // If processing base unit settings *and* Apply to CSS is turned off, then
    // send a delete() command instead. This us used when an existing theme is
    // being used and we don't want to set the base font size.
    $json_values = Json::decode($this->getJsonValues());
    if ($this->id == 'base_unit_settings' && isset($json_values['baseFontSizeEnable']) && $json_values['baseFontSizeEnable'] === FALSE) {
      $send_to_api->delete();
    }
    else {
      $send_to_api->send();
    }
    return $send_to_api;
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
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

    // Send settings to API if enabled and modified!.
    if ($this->status() && $this->isModified()) {
      $this->process();
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionLabel() {
    if (empty($this->label_collection)) {
      $label = $this->label();
      $this->label_collection = new TranslatableMarkup('@label', ['@label' => $label], [], $this->getStringTranslation());
    }
    return $this->label_collection;
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
  }

  /**
   * {@inheritdoc}
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

}
