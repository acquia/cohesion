<?php

namespace Drupal\cohesion_style_guide\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Url;

/**
 * Defines the Style guide manager entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_style_guide_manager",
 *   label = @Translation("Style guide manager"),
 *   label_singular = @Translation("Style guide manager"),
 *   label_plural = @Translation("Style guides manager"),
 *   label_collection = @Translation("Style guides manager"),
 *   label_count = @PluralTranslation(
 *     singular = "@count style guide manager",
 *     plural = "@count style guides manager",
 *   ),
 *   config_prefix = "cohesion_style_guide_manager",
 *   admin_permission = "administer style guide manager",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "theme" = "theme",
 *     "style_guide_uuid" = "style_guide_uuid",
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
 *     "theme",
 *     "style_guide_uuid"
 *   }
 * )
 */
class StyleGuideManager extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'style_guide_manager';

  /**
   * The theme it belongs to.
   *
   * @var string
   */
  protected $theme;

  /**
   * The style guide entity it belongs to.
   *
   * @var string
   */
  protected $style_guide_uuid;

  /**
   * {@inheritdoc}
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
   *
   */
  public function getApiPluginInstance() {
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function toUrl($rel = 'edit-form', array $options = []) {
    // Style guide managers are editing in the theme settings so the edit form
    // url needs to be overwritten to point to the theme settings page.
    if ($rel == 'edit-form') {
      $uri = new Url('system.theme_settings_theme', ['theme' => $this->get('theme')]);
      return $uri;
    }

    return parent::toUrl($rel, $options);
  }

}
