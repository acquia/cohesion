<?php

namespace Drupal\cohesion_sync\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the sync package entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_sync_package",
 *   label = @Translation("Package"),
 *   label_singular = @Translation("Package"),
 *   label_plural = @Translation("Packages"),
 *   label_collection = @Translation("Packages"),
 *   label_count = @PluralTranslation(
 *     singular = "@count package",
 *     plural = "@count packages",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_sync\PackageListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_sync\Form\PackageForm",
 *       "edit" = "Drupal\cohesion_sync\Form\PackageForm",
 *       "duplicate" = "Drupal\cohesion_sync\Form\PackageForm",
 *       "delete" = "Drupal\cohesion\Form\CohesionDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "access cohesion sync",
 *   config_prefix = "cohesion_sync_package",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label"
 *   },
 *   links = {
 *     "add-form" = "/admin/cohesion/sync/packages/add",
 *     "edit-form" = "/admin/cohesion/sync/packages/{cohesion_sync_package}",
 *     "delete-form" = "/admin/cohesion/sync/packages/{cohesion_sync_package}/delete",
 *     "duplicate-form" = "/admin/cohesion/sync/packages/{cohesion_sync_package}/duplicate",
 *     "collection" = "/admin/cohesion/sync/packages",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "excluded_entity_types",
 *     "settings"
 *   }
 * )
 */
class Package extends ConfigEntityBase implements PackageSettingsInterface {

  const ENTITY_MACHINE_NAME_PREFIX = 'pack_';

  /**
   * Package ID.
   *
   * @var string
   */
  protected $id;

  /**
   * Label.
   *
   * @var string
   */
  protected $label;

  /**
   * Description.
   *
   * @var string
   */
  protected $description;

  /**
   * @var mixed
   */
  protected $excluded_entity_types;

  /**
   * The entities set as requirements.
   *
   * @var mixed
   */
  protected $settings;

  /**
   * Getter.
   *
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Setter.
   *
   * @param $description
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->set('description', $description);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getExcludedEntityTypes() {
    return $this->excluded_entity_types ? json_decode($this->excluded_entity_types, TRUE) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setExcludedEntityTypes($excluded_entity_types) {
    $this->set('excluded_entity_types', json_encode($excluded_entity_types));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getSettings() {
    return $this->settings ? json_decode($this->settings, TRUE) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setSettings($settings) {
    $this->set('settings', json_encode($settings));
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    $this->delete();
  }

  /**
   * @return string
   */
  public function getEntityMachineNamePrefix() {
    return $this::ENTITY_MACHINE_NAME_PREFIX;
  }

}
