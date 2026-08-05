<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\CohesionHtmlRouteProvider;
use Drupal\cohesion_elements\Form\TagDeleteForm;
use Drupal\cohesion_elements\Form\TagForm;
use Drupal\cohesion_elements\TagsListBuilder;
use Drupal\Core\Entity\Attribute\ConfigEntityType;
use Drupal\Core\StringTranslation\TranslatableMarkup;

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
 *     "class",
 *   }
 * )
 */
#[ConfigEntityType(
  id: 'cohesion_component_tag',
  label: new TranslatableMarkup('Component Tag'),
  label_collection: new TranslatableMarkup('Component tags'),
  label_singular: new TranslatableMarkup('Component tag'),
  label_plural: new TranslatableMarkup('Component tags'),
  config_prefix: 'cohesion_component_tag',
  entity_keys: [
    'id' => 'id',
    'label' => 'label',
    'uuid' => 'uuid',
    'class' => 'class',
  ],
  handlers: [
    'list_builder' => TagsListBuilder::class,
    'form' => [
      'default' => TagForm::class,
      'add' => TagForm::class,
      'edit' => TagForm::class,
      'delete' => TagDeleteForm::class,
    ],
    'route_provider' => [
      'html' => CohesionHtmlRouteProvider::class,
    ],
  ],
  links: [
    'edit-form' => '/admin/cohesion/components/tags/{cohesion_component_tag}/edit',
    'add-form' => '/admin/cohesion/components/tags/add',
    'delete-form' => '/admin/cohesion/components/tags/{cohesion_component_tag}/delete',
    'collection' => '/admin/cohesion/components/tags',
    'in-use' => '/admin/cohesion/components/tags/{cohesion_component_tag}/in_use',
  ],
  admin_permission: 'administer component tags',
  label_count: [
    'singular' => '@count tag',
    'plural' => '@count tags',
  ],
  config_export: [
    'id',
    'label',
    'json_values',
    'json_mapper',
    'last_entity_update',
    'locked',
    'modified',
    'class',
  ],
)]
class ComponentTag extends ElementTagBase {

  const ASSET_GROUP_ID = 'cohesion_component_tag';

  const ENTITY_MACHINE_NAME_PREFIX = 'cpt_tag_';

}
