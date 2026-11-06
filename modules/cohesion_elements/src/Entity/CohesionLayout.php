<?php

namespace Drupal\cohesion_elements\Entity;

use Drupal\cohesion\Entity\EntityJsonValuesInterface;
use Drupal\cohesion\EntityJsonValuesTrait;
use Drupal\cohesion\EntityUpdateInterface;
use Drupal\cohesion_elements\CohesionLayoutInterface;
use Drupal\Component\Render\MarkupInterface;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\Query\QueryException;
use Drupal\Core\Entity\RevisionableStorageInterface;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\entity_reference_revisions\EntityNeedsSaveTrait;
use Drupal\field\Entity\FieldStorageConfig;

/**
 * Defines the CohesionLayout entity.
 *
 * @ContentEntityType(
 *   id = "cohesion_layout",
 *   label = @Translation("Layout canvas"),
 *   handlers = {
 *     "view_builder" = "Drupal\cohesion_elements\CohesionLayoutViewBuilder",
 *     "access" = "Drupal\cohesion_elements\CohesionLayoutAccessControlHandler",
 *     "workspace" = "Drupal\workspaces\Entity\Handler\IgnoredWorkspaceHandler",
 *     "form" = {
 *       "default" = "Drupal\Core\Entity\ContentEntityForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *       "edit" = "Drupal\cohesion_elements\Form\CohesionLayoutForm"
 *     },
 *     "views_data" = "Drupal\views\EntityViewsData",
 *   },
 *   entity_revision_parent_type_field = "parent_type",
 *   entity_revision_parent_id_field = "parent_id",
 *   entity_revision_parent_field_name_field = "parent_field_name",
 *   base_table = "cohesion_layout",
 *   data_table = "cohesion_layout_field_data",
 *   revision_table = "cohesion_layout_revision",
 *   revision_data_table = "cohesion_layout_field_revision",
 *   translatable = TRUE,
 *   content_translation_ui_skip = TRUE,
 *   entity_keys = {
 *     "id" = "id",
 *     "uuid" = "uuid",
 *     "revision" = "revision",
 *     "langcode" = "langcode",
 *   },
 *   common_reference_revisions_target = TRUE,
 *   default_reference_revision_settings = {
 *     "field_storage_config" = {
 *       "cardinality" = 1,
 *       "settings" = {
 *         "target_type" = "cohesion_layout"
 *       }
 *     },
 *     "field_config" = {
 *       "settings" = {
 *         "handler" = "default:cohesion_layout"
 *       }
 *     },
 *     "entity_form_display" = {
 *       "type" = "cohesion_layout_builder_widget"
 *     },
 *     "entity_view_display" = {
 *       "type" = "entity_reference_revisions_entity_view"
 *     }
 *   }
 * )
 */
class CohesionLayout extends ContentEntityBase implements CohesionLayoutInterface, EntityJsonValuesInterface, EntityUpdateInterface {

  /**
   * Static cache ID for resolved parent pointers.
   */
  protected const PARENT_CACHE_ID = self::class . '::getParentEntity';

  use EntityNeedsSaveTrait;
  use EntityJsonValuesTrait;

  /**
   * @var null
   */
  protected $host = NULL;

  /**
   * Call the API during the postSave and process methods?
   *
   * @var bool
   */
  public bool $callApi = TRUE;

  /**
   * Gets the theme manager.
   *
   * @return \Drupal\Core\Theme\ThemeManagerInterface
   */
  protected function themeManager() {
    return \Drupal::theme();
  }

  /**
   * {@inheritdoc}
   */
  public function setJsonValue($json_values) {
    $this->set('json_values', $json_values);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getJsonValues() {
    return $this->get('json_values')->value ? $this->get('json_values')->value : '{}';
  }

  /**
   * {@inheritdoc}
   */
  public function setJsonMapper($json_values) {
    $this->set('json_mapper', $json_values);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getJsonMapper() {
    return $this->get('json_mapper')->value ? $this->get('json_mapper')->value : '{}';
  }

  /**
   * @param $theme
   *   - The theme name to get the template from
   *
   * @return mixed
   */
  public function getTwig($theme = 'current') {

    if ($theme == 'current') {
      $theme = \Drupal::theme()->getActiveTheme()->getName();
    }

    if ($this->get('template')->value && is_string($this->get('template')->value)) {
      $templates = JSON::decode($this->get('template')->value);
      if (is_array($templates) && isset($templates[$theme])) {
        return Json::decode($templates[$theme])['twig'];
      }
      elseif (isset($templates['coh-generic-theme'])) {
        return Json::decode($templates['coh-generic-theme'])['twig'];
      }
    }

    return '';
  }

  /**
   * {@inheritdoc}
   */
  public function setTemplate($template) {
    $this->set('template', $template);
    return $this;
  }

  /**
   * @return mixed
   */
  public function getTwigContexts($theme = 'current') {
    if ($this->get('template')->value && isset($this->get('template')->value[$this->themeManager()->getActiveTheme()->getName()])) {
      $template = $this->get('template')->value[$this->themeManager()->getActiveTheme()->getName()];
      return Json::decode($template)['metadata']['contexts'];
    }

    return [];
  }

  /**
   * @param $theme
   *   - The theme name to get the styles from
   *
   * @return mixed
   */
  public function getStyles($theme = 'current') {
    if ($theme == 'current') {
      $theme = \Drupal::theme()->getActiveTheme()->getName();
    }
    if ($this->get('styles')->value && is_string($this->get('styles')->value)) {
      $styles = JSON::decode($this->get('styles')->value);
      if (is_array($styles) && isset($styles[$theme])) {
        return $styles[$theme];
      }
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setStyles($styles) {
    $this->set('styles', $styles);
    return $this;
  }

  /**
   * @return \Drupal\Core\Entity\ContentEntityInterface
   */
  public function getHost() {
    return $this->host;
  }

  /**
   * {@inheritdoc}
   */
  public function setHost($host) {
    if ($host instanceof ContentEntityInterface) {
      $this->host = $host;
    }
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function isLayoutCanvas() {
    return TRUE;
  }

  /**
   * @inheritDoc
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('templates_api');
  }

  /**
   * Get the parent content ID this CohesionLayout entity is referenced
   * from.
   *
   * @return $this|\Drupal\Core\Entity\ContentEntityInterface|\Drupal\Core\Entity\EntityInterface|null
   */
  public function getParentEntity() {
    $parent_type = $this->get('parent_type')->value ?? NULL;
    $parent_id = $this->get('parent_id')->value ?? NULL;

    if (!isset($parent_type) || !isset($parent_id)) {
      return NULL;
    }

    // parent_type/parent_id/parent_field_name are not revisionable - they
    // live on this entity's base table and are shared by every revision of
    // it. When more than one host entity references different revisions of
    // the same cohesion_layout entity (eg. after content was cloned and the
    // field_layout_canvas value was copied rather than regenerated), these
    // fields only ever reflect whichever host most recently saved this
    // entity, not necessarily the host that references THIS revision. Resolve
    // the actual owner of this specific revision from the parent field
    // itself first, and only fall back to the stale entity-level pointer if
    // that lookup can't find one.
    $cache = &drupal_static(self::PARENT_CACHE_ID, []);
    $langcode = $this->language()->getId();
    $cache_key = $this->id() . ':' . $this->getRevisionId() . ':' . $langcode;

    // Only a lightweight pointer is cached (never the entity itself, nor a
    // NULL result, which may become resolvable once the host is saved).
    if (!isset($cache[$cache_key])) {
      $pointer = $this->resolveParentPointer($parent_type, $parent_id);
      if ($pointer === NULL) {
        return NULL;
      }
      $cache[$cache_key] = $pointer;
    }

    $pointer = $cache[$cache_key];
    try {
      $storage = $this->entityTypeManager()->getStorage($parent_type);
      $parent = $pointer['revision_id'] !== NULL && $storage instanceof RevisionableStorageInterface
        ? $storage->loadRevision($pointer['revision_id'])
        : $storage->load($pointer['id']);
    }
    catch (PluginNotFoundException | InvalidPluginDefinitionException | EntityStorageException $e) {
      $this->logParentLookupError($e);
      return NULL;
    }

    // Return current translation of parent entity, if it exists.
    if ($parent instanceof TranslatableInterface && $parent->hasTranslation($langcode)) {
      $parent = $parent->getTranslation($langcode);
    }

    return $parent;
  }

  /**
   * Resolves which host entity owns this specific layout revision.
   *
   * @param string $parent_type
   *   The stored parent entity type ID.
   * @param string|int $parent_id
   *   The stored (possibly stale) parent entity ID.
   *
   * @return array|null
   *   An array with 'id' and 'revision_id' (NULL when the stored parent
   *   entity should be loaded as-is), or NULL if no parent was found.
   */
  protected function resolveParentPointer(string $parent_type, $parent_id): ?array {
    $parent_field_name = $this->get('parent_field_name')->value ?? NULL;

    try {
      $storage = $this->entityTypeManager()->getStorage($parent_type);
      $stale_parent = $storage->load($parent_id);
    }
    catch (PluginNotFoundException | InvalidPluginDefinitionException | EntityStorageException $e) {
      $this->logParentLookupError($e);
      return NULL;
    }

    // Cheap path: the overwhelming majority of cohesion_layout entities
    // are only ever referenced by a single host, so the stored parent_id
    // already points at the right one - and loading it is frequently a
    // static-cache hit. Confirm it still references THIS revision before
    // trusting it, which avoids the allRevisions() query below.
    if ($stale_parent instanceof FieldableEntityInterface
      && $parent_field_name
      && $stale_parent->hasField($parent_field_name)
    ) {
      $field_item = $stale_parent->get($parent_field_name)->first();
      if ($field_item
        && (string) $field_item->target_id === (string) $this->id()
        && (string) $field_item->target_revision_id === (string) $this->getRevisionId()
      ) {
        return ['id' => $parent_id, 'revision_id' => NULL];
      }
    }

    // The cheap check didn't confirm a match (either this revision is shared
    // with another host, or the stored pointer is stale) - resolve the actual
    // owner of this specific revision directly.
    if ($storage instanceof RevisionableStorageInterface && $parent_field_name) {
      try {
        $query = $storage->getQuery()
          ->allRevisions()
          ->accessCheck(FALSE)
          ->condition($parent_field_name . '.target_id', $this->id())
          ->condition($parent_field_name . '.target_revision_id', $this->getRevisionId());

        // Several host revisions can reference the same layout revision
        // (eg. a host revision that only changes metadata, not its
        // canvas), in which case range(0, 1) alone is nondeterministic.
        // Sort by the host entity type's revision key, newest first, to
        // consistently resolve to the latest such host - matching
        // CohesionLayoutViewBuilder::view().
        $revision_id_key = $storage->getEntityType()->getKey('revision');
        if ($revision_id_key) {
          $query->sort($revision_id_key, 'DESC');
        }

        $revision_ids = $query->range(0, 1)->execute();
      }
      catch (QueryException | InvalidPluginDefinitionException | PluginNotFoundException $e) {
        $this->logParentLookupError($e);
        $revision_ids = [];
      }

      if ($revision_ids) {
        $revision_id = array_key_first($revision_ids);
        return ['id' => $revision_ids[$revision_id], 'revision_id' => $revision_id];
      }
    }

    // Fall back to the stale entity-level pointer if nothing more specific
    // was found.
    return $stale_parent ? ['id' => $parent_id, 'revision_id' => NULL] : NULL;
  }

  /**
   * Logs a failure to resolve the parent entity.
   */
  protected function logParentLookupError(\Exception $e): void {
    \Drupal::logger('cohesion')->error('Unable to resolve parent of layout %id: @message', [
      '%id' => $this->id(),
      '@message' => $e->getMessage(),
    ]);
  }

  /**
   * @inheritdoc
   */
  public function jsonValuesErrors() {

    $this->resetElementsUUIDs();
    $errors = $this->validateComponentValues();

    if ($errors) {
      return $errors;
    }

    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $success = $send_to_api->sendWithoutSave();
    $responseData = $send_to_api->getData();

    // layout-field.
    if ($success === TRUE) {
      return FALSE;
    }
    else {
      return $responseData;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getLastAppliedUpdate() {
    return $this->get('last_entity_update')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setLastAppliedUpdate($callback) {
    $this->set('last_entity_update', $callback);
    return $this;
  }

  /**
   * @inheritdoc
   */
  public function process() {
    $this->preProcessJsonValues();

    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $success = $send_to_api->send();
    $responseData = $send_to_api->getData();

    // layout-field.
    if ($success === TRUE) {
      return $this->processApiResponse($responseData);
    }
    else {
      $cohesion_error = &drupal_static('entity_cohesion_error');
      $cohesion_error = $responseData['error'] ?? '';
      return $cohesion_error;
    }

  }

  /**
   * Sets the styles and template for the entity from the response from the API.
   *
   * @param array|null $responseData
   *   The API response data, or NULL if no response was received.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup|false
   *   An error message if the response was rejected, FALSE on success.
   */
  public function processApiResponse($responseData) {
    // If no API response was received — e.g. because ApiPluginBase::send()
    // short-circuited due to a guard condition such as cohesion_sync_lock or
    // isSyncing() — do not overwrite the existing template and styles fields.
    // Calling this method with empty data would unconditionally write '[]' to
    // both fields, destroying the inherited template from the cloned revision
    // and producing blank main content on the next page load with no error
    // surfaced to the editor.
    if ($responseData === NULL || $responseData === []) {
      return FALSE;
    }

    // A "successful" API response can still contain an empty compiled twig
    // for a theme (e.g. a transient compilation issue on the API side). A
    // layout canvas that actually has elements should never legitimately
    // compile to an empty twig, so treat that combination as corrupted data
    // and reject the whole response rather than persist it. A genuinely
    // empty canvas is allowed to compile to an empty twig.
    $layoutCanvas = $this->getLayoutCanvasInstance();
    if ($layoutCanvas && $layoutCanvas->hasElements()) {
      foreach ($responseData as $themeData) {
        if (isset($themeData['themeName'], $themeData['template'])) {
          $decodedTemplate = Json::decode($themeData['template']);
          if (empty($decodedTemplate['twig'])) {
            \Drupal::logger('cohesion')->error('Empty twig for theme %theme on layout %id; content kept.', [
              '%theme' => $themeData['themeName'],
              '%id' => $this->id(),
            ]);
            return t('An empty template was returned while saving; existing content has been kept.');
          }
        }
      }
    }

    $styles = [];
    $templates = [];
    $templateOnlyThemes = \Drupal::service('cohesion.utils')->getCohesionTemplateOnlyEnabledThemes();
    foreach ($responseData as $themeData) {
      if (isset($themeData['themeName'])) {
        $themeCss = Json::decode($themeData['css'])['styles'];
        // is there anything to process?
        $filterDiffs = array_filter(array_map('array_filter', $themeCss));
        if (!empty($filterDiffs)) {
          $updatedStyles = array_merge($themeCss['added'], $themeCss['updated']);

          if (!in_array($themeData['themeName'], $templateOnlyThemes)) {
            $styleValues = '';
            foreach ($updatedStyles as $cssType) {
              $styleValues .= $this->getApiPluginInstance()->processCssApiEntries($cssType);
            }

            $renderedCss = \Drupal::service('twig')->renderInline($styleValues);
            if ($renderedCss instanceof MarkupInterface) {
              $cssData = $renderedCss->__toString();
            } else {
              $cssData = $renderedCss;
            }

            $styles[$themeData['themeName']] = $cssData;
          }
        }

        if (isset($themeData['template'])) {
          $templates[$themeData['themeName']] = $themeData['template'];
        }
      }
    }

    Cache::invalidateTags(['theme_registry']);
    $this->set('styles', JSON::encode($styles));
    $this->set('template', JSON::encode($templates));

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    // Check for cached TMGMT translations. The CohesionLayoutFieldProcessor
    // stores translations in a static cache because the
    // entity_reference_revisions field item may have a different PHP object
    // for the same entity translation. Multiple PHP objects representing the
    // same translation may be saved during a single request, so we don't
    // clear the cache - it's request-scoped anyway.
    $translation_cache = &drupal_static('cohesion_layout_tmgmt_translations', []);
    $entity_id = $this->id();
    $langcode = $this->language()->getId();
    if ($entity_id && $langcode && isset($translation_cache[$entity_id][$langcode])) {
      $this->setJsonValue($translation_cache[$entity_id][$langcode]);
    }

    parent::preSave($storage);

    \Drupal::service('cohesion.entity_update_manager')->apply($this);

    $styles = $this->get('styles')->getValue();
    $template = $this->get('template')->getValue();

    if ($this->callApi) {
      $errors = $this->process();

      if ($errors) {
        // Keep original styles/template data if API error.
        \Drupal::messenger()->addMessage($errors, 'error');

        $this->set('styles', $styles);
        $this->set('template', $template);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

    drupal_static_reset(self::PARENT_CACHE_ID);
    $this->setNeedsSave(FALSE);
    $parent_entity = $this->getParentEntity();

    // Handle invalidating existing cache tags for the parent entity.
    if ($update) {
      // Get the cache tags set for this layout formatter on this entity.
      try {
        if ($parent_entity) {
          $layout_cache_tag = 'layout_formatter.' . $parent_entity->uuid();
          $entity_cache_tag = $this->getParentEntity()->getEntityTypeId() . ':' . $parent_entity->id();

          // Invalidate render cache tag for this layout formatter AND the
          // overall node.
          \Drupal::service('cache_tags.invalidator')->invalidateTags([
            $layout_cache_tag,
            $entity_cache_tag,
          ]);

          // The purge module is enabled (Ie. Acquia hosting with Vanish),
          // forceably purge the cache for this entity.
          if (\Drupal::moduleHandler()->moduleExists('purge')) {

            $purgeInvalidationFactory = \Drupal::service('purge.invalidation.factory');
            $purgeQueuers = \Drupal::service('purge.queuers');
            $purgeQueue = \Drupal::service('purge.queue');
            if ($queuer = $purgeQueuers->get('drush_purge_queue_add')) {

              $invalidations = [
                $purgeInvalidationFactory->get('tag', $layout_cache_tag),
                $purgeInvalidationFactory->get('tag', $entity_cache_tag),
              ];

              $purgeQueue->add($queuer, $invalidations);
            }
          }
        }

      }
      catch (\Exception $e) {
        return;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    drupal_static_reset(self::PARENT_CACHE_ID);
  }

  /**
   * {@inheritdoc}
   */
  public function delete() {
    // Prevent CohesionLayout entities from being deleted changing the field
    // type for layout canvas to cohesion_entity_reference_revisions in
    // cohesion_elements_update_8308.
    if (!drupal_static('cohesion_elements_update_8308', FALSE)) {
      parent::delete();
    }
  }

  /**
   * {@inheritdoc}
   *
   * Define the field properties here.
   *
   * Field name, type and size determine the table structure.
   *
   * In addition, we can define how the field and its content can be manipulated
   * in the GUI. The behaviour of the widgets used can be determined here.
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {

    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['json_values'] = BaseFieldDefinition::create('string_long')->setLabel(t('Values'))->setDefaultValue('{}')->setRevisionable(TRUE)->setTranslatable(TRUE);

    $fields['styles'] = BaseFieldDefinition::create('string_long')->setLabel(t('Styles'))->setRevisionable(TRUE)->setTranslatable(TRUE);

    $fields['template'] = BaseFieldDefinition::create('string_long')->setLabel(t('Template'))->setDefaultValue('/* */')->setRevisionable(TRUE)->setTranslatable(TRUE);

    $fields['parent_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent ID'))
      ->setDescription(t('The ID of the parent entity of which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE);

    $fields['parent_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent type'))
      ->setDescription(t('The entity parent type to which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', EntityTypeInterface::ID_MAX_LENGTH);

    $fields['parent_field_name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Parent field name'))
      ->setDescription(t('The entity parent field name to which this entity is referenced.'))
      ->setSetting('is_ascii', TRUE)
      ->setSetting('max_length', FieldStorageConfig::NAME_MAX_LENGTH);

    $fields['last_entity_update'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Last entity update callback applied'))
      ->setDescription(t('The function name of the latest EntityUpdateManager callback applied to this entity.'));

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    $uri_route_parameters['component_instance_uuid'] = $this->uuid();
    $uri_route_parameters['component_id'] = 0;

    return $uri_route_parameters;
  }

}
