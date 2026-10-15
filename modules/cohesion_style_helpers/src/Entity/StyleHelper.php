<?php

namespace Drupal\cohesion_style_helpers\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion_style_helpers\Form\StyleHelpersDeleteForm;
use Drupal\cohesion_style_helpers\Form\StyleHelpersDisableSelectionForm;
use Drupal\cohesion_style_helpers\Form\StyleHelpersEnableSelectionForm;
use Drupal\cohesion_style_helpers\Form\StyleHelpersForm;
use Drupal\cohesion_style_helpers\StyleHelpersHtmlRouteProvider;
use Drupal\cohesion_style_helpers\StyleHelpersListBuilder;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Defines the Style Helpers entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_style_helper",
 *   label = @Translation("Style helper"),
 *   label_singular = @Translation("Style helper"),
 *   label_plural = @Translation("Style helpers"),
 *   label_collection = @Translation("Style helpers"),
 *   label_count = @PluralTranslation(
 *     singular = "@count style helper",
 *     plural = "@count style helpers",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_style_helpers\StyleHelpersListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_style_helpers\Form\StyleHelpersForm",
 *       "edit" = "Drupal\cohesion_style_helpers\Form\StyleHelpersForm",
 *       "duplicate" = "Drupal\cohesion_style_helpers\Form\StyleHelpersForm",
 *       "delete" = "Drupal\cohesion_style_helpers\Form\StyleHelpersDeleteForm",
 *       "enable-selection" = "Drupal\cohesion_style_helpers\Form\StyleHelpersEnableSelectionForm",
 *       "disable-selection" = "Drupal\cohesion_style_helpers\Form\StyleHelpersDisableSelectionForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion_style_helpers\StyleHelpersHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_style_helper",
 *   admin_permission = "administer style helpers",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "bundle" = "custom_style_type",
 *     "selectable" = "selectable",
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/edit",
 *     "add-form" = "/admin/cohesion/styles/cohesion_style_helpers/add/{custom_style_type}",
 *     "delete-form" = "/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/delete",
 *     "add-page" = "/admin/cohesion/styles/cohesion_style_helpers/add",
 *     "collection" = "/admin/cohesion/styles/cohesion_style_helpers",
 *     "duplicate-form" = "/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/duplicate",
 *     "enable-selection" = "/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/enable-selection",
 *     "disable-selection" = "/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/disable-selection",
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
 *     "custom_style_type"
 *   }
 * )
 */
#[ConfigEntityType(
  id: 'cohesion_style_helper',
  label: new TranslatableMarkup('Style helper'),
  label_collection: new TranslatableMarkup('Style helpers'),
  label_singular: new TranslatableMarkup('Style helper'),
  label_plural: new TranslatableMarkup('Style helpers'),
  config_prefix: 'cohesion_style_helper',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
    'bundle' => 'custom_style_type',
    'selectable' => 'selectable',
  ],
  handlers: [
    'list_builder' => StyleHelpersListBuilder::class,
    'form' => [
      'add' => StyleHelpersForm::class,
      'edit' => StyleHelpersForm::class,
      'duplicate' => StyleHelpersForm::class,
      'delete' => StyleHelpersDeleteForm::class,
      'enable-selection' => StyleHelpersEnableSelectionForm::class,
      'disable-selection' => StyleHelpersDisableSelectionForm::class,
    ],
    'route_provider' => [
      'html' => StyleHelpersHtmlRouteProvider::class,
    ],
  ],
  links: [
    'edit-form' => '/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/edit',
    'add-form' => '/admin/cohesion/styles/cohesion_style_helpers/add/{custom_style_type}',
    'delete-form' => '/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/delete',
    'add-page' => '/admin/cohesion/styles/cohesion_style_helpers/add',
    'collection' => '/admin/cohesion/styles/cohesion_style_helpers',
    'duplicate-form' => '/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/duplicate',
    'enable-selection' => '/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/enable-selection',
    'disable-selection' => '/admin/cohesion/styles/cohesion_style_helpers/{cohesion_style_helper}/disable-selection',
  ],
  admin_permission: 'administer style helpers',
  label_count: [
    'singular' => '@count style helper',
    'plural' => '@count style helpers',
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
    'custom_style_type',
  ],
)]
class StyleHelper extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'style_helpers';

  const ENTITY_MACHINE_NAME_PREFIX = 'style_hlp_';

  /**
   * The CustomStyleType.
   *
   * @var string
   */
  protected $custom_style_type;

  /**
   * Style helper getter.
   */
  public function getCustomStyleType() {
    return $this->custom_style_type;
  }

  /**
   * @inheritdoc
   */
  public function setDefaultValues() {
    parent::setDefaultValues();

    $this->set('custom_style_type', '');
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    // Style helpers doesn't generate any data so leave this empty.
  }

  /**
   * @return array|bool
   */
  public function jsonValuesErrors() {
    /** @var \Drupal\cohesion\Plugin\Api\PreviewApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();

    // Use the style preview endpoint to validate the data.
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
   * @inheritDoc
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('preview_api');
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return FALSE;
  }

}
