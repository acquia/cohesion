<?php

namespace Drupal\cohesion_elements\Entity;

/**
 * Defines the component tag configuration entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_component_tag",
 *   label = @Translation("Component Tag"),
 *   label_singular = @Translation("Component tag"),
 *   label_plural = @Translation("Component tags"),
 *   label_collection = @Translation("Component tags"),
 *   label_count = @PluralTranslation(
 *     singular = "@count tag",
 *     plural = "@count tags",
 *   ),
 *   config_prefix = "cohesion_component_tag",
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_elements\TagsListBuilder",
 *     "form" = {
 *       "default" = "Drupal\cohesion_elements\Form\TagForm",
 *       "add" = "Drupal\cohesion_elements\Form\TagForm",
 *       "edit" = "Drupal\cohesion_elements\Form\TagForm",
 *       "delete" = "Drupal\cohesion_elements\Form\TagDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer component tags",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "class" = "class",
 *     "weight" = "weight",
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/components/tags/{cohesion_component_tag}/edit",
 *     "add-form" = "/admin/cohesion/components/tags/add",
 *     "delete-form" = "/admin/cohesion/components/tags/{cohesion_component_tag}/delete",
 *     "collection" = "/admin/cohesion/components/tags",
 *     "in-use" = "/admin/cohesion/components/tags/{cohesion_component_tag}/in_use"
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
 *     "class",
 *     "weight"
 *   }
 * )
 */
class ComponentTag extends ElementTagBase {

  const ASSET_GROUP_ID = 'cohesion_component_tag';

  const ENTITY_MACHINE_NAME_PREFIX = 'cpt_tag_';

  // Used when deleting tags that are in use.
  const TARGET_ENTITY_TYPE = 'cohesion_component';

}
