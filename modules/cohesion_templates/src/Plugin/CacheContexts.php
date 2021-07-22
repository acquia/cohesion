<?php

namespace Drupal\cohesion_templates\Plugin;

/**
 * Class CacheContexts.
 *
 * @package Drupal\cohesion_templates\Plugin
 */
class CacheContexts {

  /**
   * Return cache contexts for a given set a machine name context
   * Needs context module to be enabled.
   *
   * @param array $machine_names
   * @param array $componentFieldsValues
   *
   * @return array cache contexts
   */
  public function getFromContextName($machine_names, $componentFieldsValues = []) {
    $cache_contexts = [];

    if (is_array($machine_names)) {

      // Contexts form the context module.
      $contexts = [];

      foreach ($machine_names as $machine_name) {
        $context_name = $machine_name;
        // If the context is driven by a component field, get the values from the components field values.
        foreach ($componentFieldsValues as $componentFieldUUID => $componentField) {
          if (strpos($machine_name, $componentFieldUUID) !== FALSE) {
            $context_name = $componentField;
          }
        }

        $context_data = explode(':', $context_name);
        if (!isset($context_data[1]) || $context_data[0] == 'context') {
          $contexts[] = isset($context_data[1]) ? $context_data[1] : $context_data[0];
        }
      }

      $moduleHandler = \Drupal::service('module_handler');
      if ($moduleHandler->moduleExists('context')) {
        foreach (\Drupal::service('context.manager')->getContexts() as $context_name => $context) {
          if (in_array($context_name, $contexts)) {
            foreach ($context->getConditions() as $condition_name => $data) {
              $cache_contexts = array_merge($cache_contexts, $data->getCacheContexts());
            }
          }
        }
      }
    }

    return $cache_contexts;
  }

  /**
   * Return cache contexts for a given template entity id.
   *
   * @param $candidate_template
   * @param $componentFieldsValues
   *
   * @return array cache contexts
   */
  public function getFromTemplateEntityId($candidate_template, $componentFieldsValues = []) {
    try {
      if ($candidate_template) {
        if ($metadata = \Drupal::keyValue('coh_template_metadata')->get($candidate_template->get('twig_template'))) {
          if (isset($metadata['contexts'])) {
            return $this->getFromContextName($metadata['contexts'], $componentFieldsValues);
          }
        }
      }
    }
    catch (\Exception $e) {
    }
    return [];
  }

}
