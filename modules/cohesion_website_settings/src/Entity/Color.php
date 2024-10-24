<?php

namespace Drupal\cohesion_website_settings\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Site Studio website settings entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_color",
 *   label = @Translation("Colors"),
 *   label_singular = @Translation("Color"),
 *   label_plural = @Translation("Colors"),
 *   label_collection = @Translation("Colors"),
 *   label_count = @PluralTranslation(
 *     singular = "@count color",
 *     plural = "@count colors",
 *   ),
 *   fieldable = TRUE,
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_color",
 *   admin_permission = "administer website settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "in-use" = "/admin/cohesion/cohesion_color/{cohesion_color}/in_use",
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
 *     "selectable",
 *     "weight"
 *   }
 * )
 */
class Color extends WebsiteSettingsEntityBase implements CohesionSettingsInterface {

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
   * @var int
   */
  protected $weight;

  /**
   * Getter.
   *
   * @return int
   */
  public function getWeight() {
    return $this->weight ? $this->weight : 0;
  }

  /**
   * Setter.
   *
   * @param $weight
   */
  public function setWeight($weight) {
    $this->weight = $weight;
  }

  /**
   * Return all the icons combined for the form[].
   *
   * @return array|object|string
   */
  public function getResourceObject() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();

    return $send_to_api->getColorGroup();
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $send_to_api->send();
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

    $this->process();

    // Invalidate settings endpoint shared cache entries.
    // Cache::invalidateTags($tags);
  }

  /**
   * Return a description.
   */
  public function getLabel() {
    return $this->label;
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionLabel() {
    if (empty($this->label_collection)) {
      $label = $this->getLabel();
      $this->label_collection = new TranslatableMarkup('@label', ['@label' => $label], [], $this->getStringTranslation());
    }
    return $this->label_collection;
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This color has been tracked as in use in the places listed below. You should not delete it until you have removed its use.'),
      ],
    ];
  }

  /**
   *
   */
  public function clearData() {
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setDefaultValues() {
    parent::setDefaultValues();

    $this->modified = TRUE;
    $this->status = TRUE;
  }

}
