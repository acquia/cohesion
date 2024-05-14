<?php

namespace Drupal\cohesion_templates;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;

/**
 * Service definition for fetching CacheMetadata from Context Conditions.
 *
 * @package Drupal\cohesion_templates
 */
class ContextCacheMetadata {

  const TEMPLATE_METADATA_STORE = 'coh_template_metadata';

  /**
   * @var \Drupal\Core\KeyValueStore\KeyValueFactoryInterface
   */
  protected $keyValue;

  /**
   * @var \Drupal\context\Entity\Context[]
   */
  protected $contexts;

  /**
   * CacheMetadata constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    KeyValueFactoryInterface $keyValue,
  ) {
    $this->keyValue = $keyValue;
    if ($moduleHandler->moduleExists('context')) {
      $this->contexts = \Drupal::service('context.manager')->getContexts();
    }
  }

  /**
   * Extracts Context names from Component entity and field values.
   *
   * @param \Drupal\cohesion_elements\Entity\Component $candidate_template
   *   Component entity.
   * @param array $componentFieldsValues
   *   Field values.
   *
   * @return array
   *   Array of context names.
   */
  public function extractContextNames(CohesionConfigEntityBase $candidate_template, array $componentFieldsValues = []): array {
    $template = $candidate_template->get('twig_template');
    if ($this->contexts !== NULL && $template !== NULL) {
      if ($metadata = $this->keyValue->get(self::TEMPLATE_METADATA_STORE)->get($template)) {
        if (isset($metadata['contexts']) && is_array($metadata['contexts'])) {
          $contexts = [];

          foreach ($metadata['contexts'] as $machine_name) {
            $context_name = $machine_name;
            foreach ($componentFieldsValues as $componentFieldUUID => $componentField) {
              if (strpos($machine_name, $componentFieldUUID) !== FALSE) {
                $context_name = $componentField;
              }
            }

            $context_data = explode(':', $context_name);
            if (!isset($context_data[1]) || $context_data[0] == 'context') {
              $contexts[] = $context_data[1] ?? $context_data[0];
            }
          }

          return $contexts;
        }
      }
    }

    return [];
  }

  /**
   * Extracts cache metadata for array of context names.
   *
   * @param array $context_names
   *   Array of context names.
   *
   * @return array
   *   Cache metadata.
   */
  public function getContextsCacheMetadata(array $context_names): array {

    if ($this->contexts === NULL) {
      return [];
    }
    $cache_tags = [];
    $cache_contexts = [];

    foreach ($this->contexts as $context_name => $context) {
      if (in_array($context_name, $context_names)) {
        $cache_contexts = array_merge($cache_contexts, $context->getCacheContexts());
        $cache_tags = array_merge($cache_tags, $context->getCacheTags());
        foreach ($context->getConditions() as $data) {
          $cache_contexts = array_merge($cache_contexts, $data->getCacheContexts());
          $cache_tags = array_merge($cache_tags, $data->getCacheTags());
        }
      }
    }

    return [
      'tags' => $cache_tags,
      'contexts' => $cache_contexts,
    ];
  }

}
