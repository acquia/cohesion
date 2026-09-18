<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\CohesionHtmlRouteProvider;
use Drupal\cohesion_elements\CategoriesListBuilder;
use Drupal\cohesion_elements\Form\CategoryDeleteForm;
use Drupal\cohesion_elements\Form\CategoryForm;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
#[ConfigEntityType(
  id: 'cohesion_helper_category',
  label: new TranslatableMarkup('Helper category'),
  label_collection: new TranslatableMarkup('Helper categories'),
  label_singular: new TranslatableMarkup('Helper category'),
  label_plural: new TranslatableMarkup('Helper categories'),
  config_prefix: 'cohesion_helper_category',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
    'class' => 'class',
    'weight' => 'weight',
  ],
  handlers: [
    'list_builder' => CategoriesListBuilder::class,
    'form' => [
      'default' => CategoryForm::class,
      'add' => CategoryForm::class,
      'edit' => CategoryForm::class,
      'delete' => CategoryDeleteForm::class,
    ],
    'route_provider' => [
      'html' => CohesionHtmlRouteProvider::class,
    ],
  ],
  links: [
    'edit-form' => '/admin/cohesion/helpers/categories/{cohesion_helper_category}/edit',
    'add-form' => '/admin/cohesion/helpers/categories/add',
    'delete-form' => '/admin/cohesion/helpers/categories/{cohesion_helper_category}/delete',
    'collection' => '/admin/cohesion/helpers/categories',
    'in-use' => '/admin/cohesion/helpers/categories/{cohesion_helper_category}/in_use',
  ],
  admin_permission: 'administer helper categories',
  label_count: [
    'singular' => '@count category',
    'plural' => '@count categories',
  ],
  config_export: [
    'id',
    'label',
    'json_values',
    'json_mapper',
    'last_entity_update',
    'modified',
    'selectable',
    'class',
    'weight',
  ],
)]
class HelperCategory extends ElementCategoryBase {

  const ASSET_GROUP_ID = 'cohesion_helper_category';

  const ENTITY_MACHINE_NAME_PREFIX = 'hlp_cat_';

  // Used when deleting categories that are in use.
  const TARGET_ENTITY_TYPE = 'cohesion_helper';

  const DEFAULT_CATEGORY_ID = 'hlp_cat_uncategorized';

}
