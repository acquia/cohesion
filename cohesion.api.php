<?php

/**
 * @file
 * Hooks for the cohesion module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter raw data before it's sent to the API.
 *
 * This hook is for changes that need to be made on-the-fly, before the raw data
 * is sent to the API for processing. Changes will not be persisted to the
 * related Drupal entity. Trying to persist changes here yourself isn't a good
 * idea, the entity is likely to be in the middle of a save operation for this
 * hook to be invoked in the first place.
 *
 * If you need to make persistent changes to the data structure, use core's
 * hook_ENTITY_TYPE_presave() with one of the Site Studio config entity types,
 * or an entity type using Site Studio layout fields and/or templates.
 *
 * Due to the nature of manipulating data at this level, it's recommended that
 * usage of this hook be limited to advanced use cases only, and alterations be
 * applied with caution and sufficient test coverage.
 *
 * @param array $data
 *   Data to alter.
 * @param \Drupal\Core\Entity\EntityInterface $entity
 *   The entity data is being prepared for.
 * @param $type
 *   The type of operation. Currently one of: "style", "template",
 *   "layout_field", "elements".
 */
function hook_sitestudio_api_outbound_data_alter(array &$data, \Drupal\Core\Entity\EntityInterface $entity, $type) {

}

/**
 * @param array $data
 * @param \Drupal\Core\Entity\EntityInterface $entity
 * @param $type
 * @return void
 *
 * Deprecated function, replaced by hook_sitestudio_api_outbound_data_alter().
 *
 */
function hook_dx8_api_outbound_data_alter(array &$data, \Drupal\Core\Entity\EntityInterface $entity, $type) {

}

/**
 * Alter the token variable name for a particular entity type.
 *
 * @param $variable
 *   The variable used for the entity type
 */
function hook_sitestudio_ENTITY_TYPE_drupal_token_context_alter(&$variable) {
  $variable = 'product_entity';
}

/**
 * @param $variable
 * @return void
 *
 * Deprecated function, replaced by
 * hook_sitestudio_ENTITY_TYPE_drupal_token_context_alter().
 */
function hook_dx8_ENTITY_TYPE_drupal_token_context_alter(&$variable) {
  $variable = 'product_entity';
}

/**
 * Alter the variable name where the main content is stored for a particular
 * entity type.
 *
 * @param $variable
 *   The variable used for the entity type
 */
function hook_sitestudio_ENTITY_TYPE_drupal_field_prefix_alter(&$variable) {
  // By default, the content of the page is stored in the twig variable content
  // But this not a strict rule and other module can store the main information
  // inside different variables. For example Commerce stores every field of
  // their product inside the product variable.
  $variable = 'product';
}

/**
 * @param $variable
 * @return void
 *
 * Deprecated function, replaced by
 * hook_sitestudio_ENTITY_TYPE_drupal_field_prefix_alter().
 */
function hook_dx8_ENTITY_TYPE_drupal_field_prefix_alter(&$variable) {
  $variable = 'product';
}

/**
 * Alter the list of available twig variables available on the field element
 * for a specific entity type.
 *
 * @param array $variables
 */
function hook_sitestudio_ENTITY_TYPE_drupal_field_variable_alter(&$variables) {
  $variables[] = [
    'value' => 'my_twig_variable',
    'name' => 'My twig variable',
  ];
}

/**
 * @param $variables
 * @return void
 *
 * Deprecated function, replaced by
 * hook_sitestudio_ENTITY_TYPE_drupal_field_variable_alter().
 */
function hook_dx8_ENTITY_TYPE_drupal_field_variable_alter(&$variables) {
  $variables[] = [
    'value' => 'my_twig_variable',
    'name' => 'My twig variable',
  ];
}

/**
 * Alter the list of available twig variables available on the field element
 * for a specific entity type and bundle.
 *
 * @param array $variables
 */
function hook_sitestudio_ENTITY_TYPE_BUNDLE_drupal_field_variable_alter(&$variables) {
  $variables[] = [
    'value' => 'my_twig_variable',
    'name' => 'My twig variable',
  ];
}

/**
 * @param $variables
 * @return void
 *
 * Deprecated function, replaced by
 * hook_sitestudio_ENTITY_TYPE_BUNDLE_drupal_field_variable_alter().
 */
function hook_dx8_ENTITY_TYPE_BUNDLE_drupal_field_variable_alter(&$variables) {
  $variables[] = [
    'value' => 'my_twig_variable',
    'name' => 'My twig variable',
  ];
}

/**
 * Alter the list of available twig variables available on the field element
 * for all entity types.
 *
 * @param array $variables
 */
function hook_sitestudio_drupal_field_variable_alter(&$variables) {
  $variables[] = [
    'value' => 'my_twig_variable',
    'name' => 'My twig variable',
  ];
}

/**
 * @param $variables
 * @return void
 *
 * Deprecated function, replaced by
 * hook_sitestudio_drupal_field_variable_alter().
 */
function hook_dx8_drupal_field_variable_alter(&$variables) {
  $variables[] = [
    'value' => 'my_twig_variable',
    'name' => 'My twig variable',
  ];
}

/**
 * @} End of "addtogroup hooks".
 */
