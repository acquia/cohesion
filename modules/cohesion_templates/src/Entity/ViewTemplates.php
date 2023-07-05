<?php

namespace Drupal\cohesion_templates\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;

/**
 * Defines the Site Studio view templates entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_view_templates",
 *   label = @Translation("View template"),
 *   label_singular = @Translation("View template"),
 *   label_plural = @Translation("View templates"),
 *   label_collection = @Translation("View templates"),
 *   label_count = @PluralTranslation(
 *     singular = "@count view template",
 *     plural = "@count view templates",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_templates\TemplatesListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_templates\Form\ViewTemplatesForm",
 *       "edit" = "Drupal\cohesion_templates\Form\ViewTemplatesForm",
 *       "duplicate" = "Drupal\cohesion_templates\Form\ViewTemplatesForm",
 *       "delete" = "Drupal\cohesion_templates\Form\TemplateDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_view_templates",
 *   admin_permission = "administer view templates",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/templates/view_templates/{cohesion_view_templates}/edit",
 *     "add-form" = "/admin/cohesion/templates/view_templates/add",
 *     "delete-form" = "/admin/cohesion/templates/view_templates/{cohesion_view_templates}/delete",
 *     "collection" = "/admin/cohesion/templates/view_templates",
 *     "duplicate-form" = "/admin/cohesion/templates/view_templates/{cohesion_view_templates}/duplicate",
 *     "in-use" = "/admin/cohesion/templates/view_templates/{cohesion_view_templates}/in_use",
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
class ViewTemplates extends CohesionTemplateBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'view_template';

  const ENTITY_MACHINE_NAME_PREFIX = 'view_tpl_';

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This view template has been tracked as in use in the places listed below. You should not delete it until you have removed its use.'),
      ],
    ];
  }

}
