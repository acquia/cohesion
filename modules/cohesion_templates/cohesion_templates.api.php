<?php

/**
 * @file
 * Callbacks and hooks related to cohesion templates.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Alter the base hook name for a particular entity type.
 *
 * @param string $base_hook
 *   The base hook used for the entity type.
 */
function hook_cohesion_templates_ENTITY_TYPE_base_hook_alter(&$base_hook) {
  // Alter the theme hook name for the entity type
  // By default it will be the ID of the entity type
  // This hook is useful when the entity is not responsible for its
  // own rendering
  // For example block_content are rendered by blocks so the hook
  // name as to be altered.
  $base_hook = 'block';
}

/**
 * @} End of "addtogroup hooks".
 */
