<?php

namespace Drupal\cohesion_templates;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\KeyValueStore\KeyValueFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Cache of template metadata, scoped to the current request.
   *
   * @var array
   */
  protected $metadataCache = [];

  /**
   * The request ID to detect when we move to a new request.
   *
   * @var string
   */
  protected $currentRequestId;

  /**
   * CacheMetadata constructor.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\KeyValueStore\KeyValueFactoryInterface $keyValue
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   */
  public function __construct(
    ModuleHandlerInterface $moduleHandler,
    KeyValueFactoryInterface $keyValue,
    RequestStack $requestStack,
  ) {
    $this->keyValue = $keyValue;
    $this->requestStack = $requestStack;
    if ($moduleHandler->moduleExists('context')) {
      $this->contexts = \Drupal::service('context.manager')->getContexts();
    }
  }

  /**
   * Ensures the metadata cache is reset for each new request.
   *
   * Uses a sentinel value for no-request scenarios (CLI, cron, queue) so that
   * repeated calls in the same no-request context retain the cache, only
   * clearing when transitioning between request and no-request states.
   *
   * @return void
   */
  protected function ensureRequestCacheIsValid(): void {
    $request = $this->requestStack->getCurrentRequest();

    // Use object ID for real requests, or a sentinel string for no-request.
    // This ensures we only clear the cache when transitioning between states,
    // not on every call in a no-request context (e.g., CLI, cron, queue).
    $requestId = $request ? spl_object_id($request) : 'no-request';

    if ($this->currentRequestId !== $requestId) {
      $this->metadataCache = [];
      $this->currentRequestId = $requestId;
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
      // Ensure the metadata cache is valid for this request.
      $this->ensureRequestCacheIsValid();

      // Memoize key_value reads per twig template slug for the duration of
      // the request. extractContextNames() is called once per Cohesion
      // component and menu rendered on a page, so a single page with many
      // component instances causes repeated identical reads from the
      // coh_template_metadata key_value store.
      if (!array_key_exists($template, $this->metadataCache)) {
        $this->metadataCache[$template] = $this->keyValue->get(self::TEMPLATE_METADATA_STORE)->get($template);
      }
      if ($metadata = $this->metadataCache[$template]) {
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
