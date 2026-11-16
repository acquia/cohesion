<?php

namespace Drupal\cohesion_elements\Entity;

/**
 * Defines the helper category configuration entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_helper_category",
 *   label = @Translation("Helper category"),
 *   label_singular = @Translation("Helper category"),
 *   label_plural = @Translation("Helper categories"),
 *   label_collection = @Translation("Helper categories"),
 *   label_count = @PluralTranslation(
 *     singular = "@count category",
 *     plural = "@count categories",
 *   ),
 *   config_prefix = "cohesion_helper_category",
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
 *   admin_permission = "administer helper categories",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "class" = "class",
 *     "weight" = "weight",
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/helpers/categories/{cohesion_helper_category}/edit",
 *     "add-form" = "/admin/cohesion/helpers/categories/add",
 *     "delete-form" = "/admin/cohesion/helpers/categories/{cohesion_helper_category}/delete",
 *     "collection" = "/admin/cohesion/helpers/categories",
 *     "in-use" = "/admin/cohesion/helpers/categories/{cohesion_helper_category}/in_use"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "json_values",
 *     "json_mapper",
 *     "last_entity_update",
 *     "modified",
 *     "selectable",
 *     "class",
 *     "weight"
 *   }
 * )
 */
class HelperCategory extends ElementCategoryBase {

  const ASSET_GROUP_ID = 'cohesion_helper_category';

  const ENTITY_MACHINE_NAME_PREFIX = 'hlp_cat_';

  // Used when deleting categories that are in use.
  const TARGET_ENTITY_TYPE = 'cohesion_helper';

  const DEFAULT_CATEGORY_ID = 'hlp_cat_uncategorized';

}
