<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\CohesionHtmlRouteProvider;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion_elements\ElementsListBuilder;
use Drupal\cohesion_elements\Form\HelperDeleteForm;
use Drupal\cohesion_elements\Form\HelperDisableSelectionForm;
use Drupal\cohesion_elements\Form\HelperEnableSelectionForm;
use Drupal\cohesion_elements\Form\HelperForm;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Site Studio helper entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_helper",
 *   label = @Translation("Helper"),
 *   label_singular = @Translation("Helper"),
 *   label_plural = @Translation("Helpers"),
 *   label_collection = @Translation("Helpers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count helper",
 *     plural = "@count helpers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_elements\ElementsListBuilder",
 *     "form" = {
 *       "default" = "Drupal\cohesion_elements\Form\HelperForm",
 *       "add" = "Drupal\cohesion_elements\Form\HelperForm",
 *       "edit" = "Drupal\cohesion_elements\Form\HelperForm",
 *       "duplicate" = "Drupal\cohesion_elements\Form\HelperForm",
 *       "delete" = "Drupal\cohesion_elements\Form\HelperDeleteForm",
 *       "enable-selection" = "Drupal\cohesion_elements\Form\HelperEnableSelectionForm",
 *       "disable-selection" = "Drupal\cohesion_elements\Form\HelperDisableSelectionForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_helper",
 *   admin_permission = "administer helpers",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "selectable" = "selectable",
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/helpers/helpers/{cohesion_helper}/edit",
 *     "add-form" = "/admin/cohesion/helpers/helpers/add",
 *     "delete-form" = "/admin/cohesion/helpers/helpers/{cohesion_helper}/delete",
 *     "collection" = "/admin/cohesion/helpers/helpers",
 *     "duplicate-form" = "/admin/cohesion/helpers/helpers/{cohesion_helper}/duplicate",
 *     "enable-selection" = "/admin/cohesion/helpers/helpers/{cohesion_helper}/enable-selection",
 *     "disable-selection" = "/admin/cohesion/helpers/helpers/{cohesion_helper}/disable-selection",
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
 *     "entity_type_access",
 *     "bundle_access",
 *     "weight"
 *   }
 * )
 */
#[ConfigEntityType(
  id: 'cohesion_helper',
  label: new TranslatableMarkup('Helper'),
  label_collection: new TranslatableMarkup('Helpers'),
  label_singular: new TranslatableMarkup('Helper'),
  label_plural: new TranslatableMarkup('Helpers'),
  config_prefix: 'cohesion_helper',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
    'selectable' => 'selectable',
  ],
  handlers: [
    'list_builder' => ElementsListBuilder::class,
    'form' => [
      'default' => HelperForm::class,
      'add' => HelperForm::class,
      'edit' => HelperForm::class,
      'duplicate' => HelperForm::class,
      'delete' => HelperDeleteForm::class,
      'enable-selection' => HelperEnableSelectionForm::class,
      'disable-selection' => HelperDisableSelectionForm::class,
    ],
    'route_provider' => [
      'html' => CohesionHtmlRouteProvider::class,
    ],
  ],
  links: [
    'edit-form' => '/admin/cohesion/helpers/helpers/{cohesion_helper}/edit',
    'add-form' => '/admin/cohesion/helpers/helpers/add',
    'delete-form' => '/admin/cohesion/helpers/helpers/{cohesion_helper}/delete',
    'collection' => '/admin/cohesion/helpers/helpers',
    'duplicate-form' => '/admin/cohesion/helpers/helpers/{cohesion_helper}/duplicate',
    'enable-selection' => '/admin/cohesion/helpers/helpers/{cohesion_helper}/enable-selection',
    'disable-selection' => '/admin/cohesion/helpers/helpers/{cohesion_helper}/disable-selection',
  ],
  admin_permission: 'administer helpers',
  label_count: [
    'singular' => '@count helper',
    'plural' => '@count helpers',
  ],
  config_export: [
    'id',
    'label',
    'json_values',
    'json_mapper',
    'last_entity_update',
    'locked',
    'modified',
    'selectable',
    'category',
    'preview_image',
    'entity_type_access',
    'bundle_access',
    'weight',
  ],
)]
class Helper extends CohesionElementEntityBase implements CohesionSettingsInterface, CohesionElementSettingsInterface {

  const ASSET_GROUP_ID = 'helper';

  const CATEGORY_ENTITY_TYPE_ID = 'cohesion_helper_category';

  const ENTITY_MACHINE_NAME_PREFIX = 'hlp_';

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    // Helpers have no data to be cleared.
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $this->setModified(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function getAssetName() {
    $form = FALSE;

    // Scan the canvas to find out if it's a form or not.
    if ($this->id() && $top_type = $this->getTopType()) {
      $form = strstr($top_type, 'form-');
    }
    // New entity.
    else {
      if (\Drupal::request()->query->get('asset-name') == 'helper-form') {
        $form = TRUE;
      }
    }

    return $form ? 'helper-form' : 'helper';
  }

}
