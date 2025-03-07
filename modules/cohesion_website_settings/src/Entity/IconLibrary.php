<?php

namespace Drupal\cohesion_website_settings\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\EntityHasResourceObjectTrait;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Site Studio website settings entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_icon_library",
 *   label = @Translation("Icon library"),
 *   label_singular = @Translation("Icon library"),
 *   label_plural = @Translation("Icon libraries"),
 *   label_collection = @Translation("Icon libraries"),
 *   label_count = @PluralTranslation(
 *     singular = "@count icon library",
 *     plural = "@count icon libraries",
 *   ),
 *   fieldable = TRUE,
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_icon_library",
 *   admin_permission = "administer website settings",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "in-use" = "/admin/cohesion/cohesion_icon_library/{cohesion_icon_library}/in_use",
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
class IconLibrary extends WebsiteSettingsEntityBase implements CohesionSettingsInterface {

  use EntityHasResourceObjectTrait;
  use WebsiteSettingsSourceTrait;
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
   * Return all the icons combined for the form[].
   *
   * @return array|object|string
   */
  public function getResourceObject() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();

    return $send_to_api->getIconGroup();
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if ($this->getOriginalId()) {
      $original = $storage->loadUnchanged($this->getOriginalId());
      if ($original instanceof IconLibrary) {
        $original_json_values = $original->getDecodedJsonValues();
        $json_values = $this->getDecodedJsonValues();

        // Clear the previous font files if the new icon library files are
        // different or as no files.
        if (isset($json_values['fontFiles'])) {
          if ((isset($original_json_values['fontFiles']) && $json_values['fontFiles'] != $original_json_values['fontFiles'])) {
            $this->clearIconFontFiles($original_json_values, $json_values);
          }
        }
        else {
          $this->clearIconFontFiles($original_json_values);
        }

        if (isset($json_values['iconJSON']['json'])) {
          if ((isset($original_json_values['iconJSON']['json']) && $json_values['iconJSON']['json'] != $original_json_values['iconJSON']['json'])) {
            $this->clearSelectionJson($original_json_values, $json_values);
          }
        }
        else {
          $this->clearSelectionJson($original_json_values);
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    /** @var \Drupal\cohesion_website_settings\Plugin\Api\WebsiteSettingsApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $send_to_api->send();
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    // Send settings to API if enabled and modified!.
    // && $this->isModified()) {.
    if ($this->status()) {
      $this->process();
    }

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
    return ['message' => ['#markup' => t('This icon library has been tracked as in use in the places listed below. You should not delete it until you have removed its use.')]];
  }

  /**
   *
   */
  public function clearData() {
    $json_value = $this->getDecodedJsonValues();
    $this->clearIconFontFiles($json_value);
    $this->clearSelectionJson($json_value);

  }

  /**
   * Clear font files for a given json values of a icon library.
   *
   * @param $json_value
   * @param $new_json_values
   */
  private function clearIconFontFiles($json_value, $new_json_values = NULL) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    if (isset($json_value['fontFiles']) && is_array($json_value['fontFiles'])) {
      foreach ($json_value['fontFiles'] as $fontFile) {
        if (is_array($fontFile) && isset($fontFile['uri']) && file_exists($fontFile['uri'])) {
          $should_delete = TRUE;
          // Only delete if file have not the same path.
          if ($new_json_values && isset($new_json_values['fontFiles']) && is_array($new_json_values['fontFiles'])) {
            foreach ($new_json_values['fontFiles'] as $newFontFile) {
              $ll = $file_system->realpath($newFontFile['uri']);
              $ss = $file_system->realpath($fontFile['uri']);
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
   * Clear selection json for a given json values of a icon library.
   *
   * @param $json_value
   * @param $new_json_values
   */
  private function clearSelectionJson($json_value, $new_json_values = NULL) {
    /** @var \Drupal\Core\File\FileSystemInterface $file_system */
    $file_system = \Drupal::service('file_system');
    if (isset($json_value['iconJSON']['json'])) {
      if (!$new_json_values || !isset($new_json_values['iconJSON']['json']) || $file_system->realpath($json_value['iconJSON']['json']) != $file_system->realpath($json_value['iconJSON']['json'])) {
        \Drupal::service('cohesion.local_files_manager')->deleteFileByURI($json_value['iconJSON']['json']);
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

}
