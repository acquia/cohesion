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
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
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

  use EntityNeedsSaveTrait;
  use EntityJsonValuesTrait;

  /**
   * @var null
   */
  protected $host = NULL;

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
    if (!isset($this->get('parent_type')->value) || !isset($this->get('parent_id')->value)) {
      return NULL;
    }

    try {
      $parent = $this->entityTypeManager()->getStorage($this->get('parent_type')->value)->load($this->get('parent_id')->value);
    }
    catch (\Exception $e) {
      return NULL;
    }

    // Return current translation of parent entity, if it exists.
    if ($parent != NULL && ($parent instanceof TranslatableInterface) && $parent->hasTranslation($this->language()->getId())) {
      return $parent->getTranslation($this->language()->getId());
    }

    return $parent;
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
      $this->processApiResponse($responseData);
      return FALSE;
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
   * @param array $responseData
   */
  public function processApiResponse($responseData) {
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
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);

    \Drupal::service('cohesion.entity_update_manager')->apply($this);

    $styles = $this->get('styles')->getValue();
    $template = $this->get('template')->getValue();

    $errors = $this->process();

    if ($errors) {
      // Keep original styles/template data if API error.
      \Drupal::messenger()->addMessage($errors, 'error');

      $this->set('styles', $styles);
      $this->set('template', $template);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage, $update);

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
