<?php

namespace Drupal\cohesion_templates\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;

/**
 * Defines the Site Studio menu templates entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_menu_templates",
 *   label = @Translation("Menu template"),
 *   label_singular = @Translation("menu template"),
 *   label_plural = @Translation("Menu templates"),
 *   label_collection = @Translation("Menu templates"),
 *   label_count = @PluralTranslation(
 *     singular = "@count menu template",
 *     plural = "@count menu templates",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_templates\TemplatesListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_templates\Form\MenuTemplatesForm",
 *       "edit" = "Drupal\cohesion_templates\Form\MenuTemplatesForm",
 *       "duplicate" = "Drupal\cohesion_templates\Form\MenuTemplatesForm",
 *       "delete" = "Drupal\cohesion_templates\Form\TemplateDeleteForm",
 *       "enable-selection" = "Drupal\cohesion\Form\CohesionEnableSelectionForm",
 *       "disable-selection" = "Drupal\cohesion\Form\CohesionDisableSelectionForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_menu_templates",
 *   admin_permission = "administer menu templates",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "selectable" = "selectable"
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/templates/menu_templates/{cohesion_menu_templates}/edit",
 *     "add-form" = "/admin/cohesion/templates/menu_templates/add",
 *     "delete-form" = "/admin/cohesion/templates/menu_templates/{cohesion_menu_templates}/delete",
 *     "collection" = "/admin/cohesion/templates/menu_templates",
 *     "duplicate-form" = "/admin/cohesion/templates/menu_templates/{cohesion_menu_templates}/duplicate",
 *     "enable-selection" = "/admin/cohesion/templates/menu_templates/{cohesion_menu_templates}/enable-selection",
 *     "disable-selection" = "/admin/cohesion/templates/menu_templates/{cohesion_menu_templates}/disable-selection",
 *     "in-use" = "/admin/cohesion/templates/menu_templates/{cohesion_menu_templates}/in_use",
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
 *     "custom",
 *     "twig_template"
 *   }
 * )
 */
class MenuTemplates extends CohesionTemplateBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'menu_template';

  const ENTITY_MACHINE_NAME_PREFIX = 'menu_tpl_';

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This menu template has been tracked as in use in the places listed below. You should not delete it until you have removed its use.'),
      ],
    ];
  }

}
