<?php

namespace Drupal\cohesion_website_settings\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Site Studio website settings entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_font_library",
 *   label = @Translation("Font library"),
 *   label_singular = @Translation("Font library"),
 *   label_plural = @Translation("Font libraries"),
 *   label_collection = @Translation("Font libraries"),
 *   label_count = @PluralTranslation(
 *     singular = "@count font library",
 *     plural = "@count font libraries",
 *   ),
 *   fieldable = TRUE,
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_font_library",
 *   admin_permission = "administer website settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "in-use" = "/admin/cohesion/cohesion_font_library/{cohesion_font_library}/in_use",
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
 *     "source"
 *   }
 * )
 */
class FontLibrary extends WebsiteSettingsEntityBase implements CohesionSettingsInterface {

  use WebsiteSettingsSourceTrait;

  const ASSET_GROUP_ID = 'website_settings';

  /**
   * Return all the icons combined for the form[].
   *
   * @return array|object|string
   */
  public function getResourceObject() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();

    return $send_to_api->getFontGroup();
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $send_to_api->send();
    $send_to_api->getData();
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    if ($this->getOriginalId()) {
      $original = $storage->loadUnchanged($this->getOriginalId());
      if ($original instanceof FontLibrary) {
        $original_json_values = $original->getDecodedJsonValues();
        $json_values = $this->getDecodedJsonValues();

        // Set the entity label from the name inside the JSON.
        $this->setlabel($json_values['name']);

        // Clear the previous font files if the new font library files are
        // different or as no files.
        if (isset($json_values['fontFiles'])) {
          if ((isset($original_json_values['fontFiles']) && $json_values['fontFiles'] != $original_json_values['fontFiles'])) {
            $this->clearFontFiles($original_json_values, $json_values);
          }
        }
        else {
          $this->clearFontFiles($original_json_values);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Send settings to API if enabled and modified!.
    $this->process();

    // Invalidate settings endpoint shared cache entries.
    $tags = ('font_libraries' == $this->id()) ? [$this->id(), 'font_stack'] : [$this->id()];
    Cache::invalidateTags($tags);
  }

  /**
   * Set a description.
   */
  public function setLabel($label) {
    $this->label = $label;
    return $this;
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
  public function getInUseMessage() {
    return ['message' => ['#markup' => t('This font library has been tracked as in use in the places listed below. You should not delete it until you have removed its use.')]];
  }

  /**
   *
   */
  public function clearData() {
    $json_value = $this->getDecodedJsonValues();
    $this->clearFontFiles($json_value);
  }

  /**
   * Clear font files for a given json values of a font library.
   *
   * @param $json_value
   * @param $new_json_values
   */
  private function clearFontFiles($json_value, $new_json_values = NULL) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    if (isset($json_value['fontFiles']) && is_array($json_value['fontFiles'])) {
      foreach ($json_value['fontFiles'] as $fontFile) {
        if (is_array($fontFile) && isset($fontFile['uri']) && file_exists($fontFile['uri'])) {

          $should_delete = TRUE;
          // Only delete if file have not the same path.
          if ($new_json_values && isset($new_json_values['fontFiles']) && is_array($new_json_values['fontFiles'])) {
            foreach ($new_json_values['fontFiles'] as $newFontFile) {
              if ($file_system->realpath($newFontFile['uri']) == $file_system->realpath($fontFile['uri'])) {
                $should_delete = FALSE;
              }
            }
          }

          if ($should_delete) {
            \Drupal::service('cohesion.local_files_manager')->deleteFileByURI($fontFile['uri']);
          }
        }
      }
    }
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
