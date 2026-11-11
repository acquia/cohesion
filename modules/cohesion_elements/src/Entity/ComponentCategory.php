<?php

namespace Drupal\cohesion_elements\Entity;

/**
 * Defines the component category configuration entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_component_category",
 *   label = @Translation("Component category"),
 *   label_singular = @Translation("Component category"),
 *   label_plural = @Translation("Component categories"),
 *   label_collection = @Translation("Component categories"),
 *   label_count = @PluralTranslation(
 *     singular = "@count category",
 *     plural = "@count categories",
 *   ),
 *   config_prefix = "cohesion_component_category",
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_elements\CategoriesListBuilder",
 *     "form" = {
 *       "default" = "Drupal\cohesion_elements\Form\CategoryForm",
 *       "add" = "Drupal\cohesion_elements\Form\CategoryForm",
 *       "edit" = "Drupal\cohesion_elements\Form\CategoryForm",
 *       "delete" = "Drupal\cohesion_elements\Form\CategoryDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   admin_permission = "administer component categories",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "class" = "class",
 *     "weight" = "weight",
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/components/categories/{cohesion_component_category}/edit",
 *     "add-form" = "/admin/cohesion/components/categories/add",
 *     "delete-form" = "/admin/cohesion/components/categories/{cohesion_component_category}/delete",
 *     "collection" = "/admin/cohesion/components/categories",
 *     "in-use" = "/admin/cohesion/components/categories/{cohesion_component_category}/in_use"
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
class ComponentCategory extends ElementCategoryBase {

  const ASSET_GROUP_ID = 'cohesion_component_category';

  const ENTITY_MACHINE_NAME_PREFIX = 'cpt_cat_';

  // Used when deleting categories that are in use.
  const TARGET_ENTITY_TYPE = 'cohesion_component';

  const DEFAULT_CATEGORY_ID = 'cpt_cat_uncategorized';

}
