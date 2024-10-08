<?php

namespace Drupal\cohesion_templates\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the Site Studio content templates entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_content_templates",
 *   label = @Translation("Content templates"),
 *   label_singular = @Translation("Content template"),
 *   label_plural = @Translation("Content templates"),
 *   label_collection = @Translation("Content templates"),
 *   label_count = @PluralTranslation(
 *     singular = "@count content template",
 *     plural = "@count content templates",
 *   ),
 *   handlers = {
 *     "list_builder" =
 *   "Drupal\cohesion_templates\ContentTemplatesListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_templates\Form\ContentTemplatesForm",
 *       "edit" = "Drupal\cohesion_templates\Form\ContentTemplatesForm",
 *       "duplicate" = "Drupal\cohesion_templates\Form\ContentTemplatesForm",
 *       "delete" = "Drupal\cohesion_templates\Form\TemplateDeleteForm",
 *       "enable" = "Drupal\cohesion_templates\Form\TemplateEnableForm",
 *       "disable" =
 *   "Drupal\cohesion_templates\Form\ContentTemplateDisableForm",
 *       "enable-selection" =
 *   "Drupal\cohesion\Form\CohesionEnableSelectionForm",
 *       "disable-selection" =
 *   "Drupal\cohesion\Form\CohesionDisableSelectionForm",
 *       "set_default" =
 *   "Drupal\cohesion_templates\Form\ContentTemplatesSetDefaultForm",
 *     },
 *     "route_provider" = {
 *       "html" =
 *   "Drupal\cohesion_templates\CohesionContentTemplateHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_content_templates",
 *   admin_permission = "administer content templates",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "selectable" = "selectable"
 *   },
 *   links = {
 *     "edit-form" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/edit",
 *     "add-form" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{bundle}/add",
 *     "delete-form" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/delete",
 *     "collection" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}",
 *     "duplicate-form" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/duplicate",
 *     "disable" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/disable",
 *     "enable" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/enable",
 *     "enable-selection" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/enable-selection",
 *     "disable-selection" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/disable-selection",
 *     "set-default-form" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/set_default",
 *     "in-use" =
 *   "/admin/cohesion/templates/content_templates/{content_entity_type}/{cohesion_content_templates}/in_use",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "json_values",
 *     "json_mapper",
 *     "last_entity_update",
 *     "modified",
 *     "selectable",
 *     "custom",
 *     "twig_template",
 *     "default",
 *     "entity_type",
 *     "bundle",
 *     "view_mode",
 *     "master_template"
 *   }
 * )
 */
class ContentTemplates extends CohesionTemplateBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'content_template';

  /**
   * The bundle identifier.
   *
   * @var string
   */
  protected $bundle;

  /**
   * The entity type identifier.
   *
   * @var string
   */
  protected $entity_type;

  /**
   * Return the set view mode for this content template (used for views query).
   *
   * @return mixed|null
   */
  public function getViewMode() {
    return $this->get('view_mode');
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage);

    if ($this->get('default') === TRUE && $this->get('view_mode') === 'full' && $this->get('bundle') !== '__any__') {

      $default_templates_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
        ->accessCheck(TRUE)
        ->condition('entity_type', $this->get('entity_type'))
        ->condition('bundle', $this->get('bundle'))
        ->condition('view_mode', $this->get('view_mode'))
        ->condition('default', TRUE)
        ->condition('id', $this->id(), '<>')
        ->execute();

      $default_templates = $this->loadMultiple($default_templates_ids);

      foreach ($default_templates as $default_template) {
        $default_template->set('default', FALSE);
        $default_template->save();
      }
    }
  }

  /**
   * @inheritdoc
   */
  public function setDefaultValues() {
    parent::setDefaultValues();

    $this->set('master_template', '');
    $this->set('default', FALSE);
  }

  /**
   * Import a list of entities.
   *
   * Maintain the list of content templates.
   * This should be run whenever a entity type or view mode is added or removed,
   *  or regularly via cron().
   *
   * @param array $entities
   *   Tree of entity types with respective bundles and view modes.
   */
  public static function importEntities(array $entities) {
    $canonical_list = [];
    foreach ($entities as $entity_type => $entity) {
      foreach ($entity['bundles'] as $bundle_id => $bundle) {
        foreach ($entity['view_modes'] as $entity_view_mode) {
          // Try to import entity.
          [$entity_type_id, $view_mode] = explode('.', $entity_view_mode['id']);
          // Determine if has already been imported by finding some existing
          // templates in DB.
          $already_imported_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
            ->accessCheck(TRUE)
            ->condition('entity_type', $entity_type)
            ->condition('bundle', $bundle_id)
            ->condition('view_mode', $view_mode)
            ->execute();

          if (count($already_imported_ids) <= 0) {
            $entity_id = self::generateUniqueEntityId($entity_type_id . '_' . $bundle_id . '_' . $view_mode, 32);
            $new_entity = self::create([
              'id' => $entity_id,
              'label' => $entity_view_mode['label'] . ' (' . ucwords($entity_type) . ', ' . $bundle['label'] . ')',
              'entity_type' => $entity_type,
              'bundle' => $bundle_id,
              'view_mode' => $view_mode,
            ]);
            $new_entity->setDefaultValues();
            $new_entity->save();
            $canonical_list[$entity_id] = $entity_id;
          }
          else {
            foreach ($already_imported_ids as $entity_id) {
              $canonical_list[$entity_id] = $entity_id;
            }
          }
        }
      }

      unset($entity_view_mode);

      $entity_types = \Drupal::service('entity_type.manager')->getDefinitions();
      $hasBundle = $entity_types[$entity_type]->hasKey('bundle');

      // Create global view mode template for entities with multiple bundles.
      if ($hasBundle) {
        foreach ($entity['view_modes'] as $entity_view_mode) {
          $bundle_id = '__any__';
          [$entity_type_id, $view_mode] = explode('.', $entity_view_mode['id']);

          $already_imported_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
            ->accessCheck(TRUE)
            ->condition('entity_type', $entity_type)
            ->condition('bundle', $bundle_id)
            ->condition('view_mode', $view_mode)
            ->execute();

          if (count($already_imported_ids) <= 0) {
            $entity_id = self::generateUniqueEntityId($entity_type_id . '_' . $bundle_id . '_' . $view_mode, 32);
            $entity = self::create([
              'id' => $entity_id,
              'label' => $entity_view_mode['label'] . ' (' . ucwords($entity_type) . ')',
              'entity_type' => $entity_type,
              'bundle' => $bundle_id,
              'view_mode' => $view_mode,
            ]);
            $entity->setDefaultValues();
            $entity->save();
            $canonical_list[$entity_id] = $entity_id;
          }
          else {
            foreach ($already_imported_ids as $entity_id) {
              $canonical_list[$entity_id] = $entity_id;
            }
          }
        }
      }
    }

    // Remove old entities.
    $current_entities = self::loadMultiple();
    foreach ($current_entities as $current_entity) {
      if (!array_key_exists($current_entity->id(), $canonical_list)) {
        $current_entity->delete();
      }
    }
  }

  /**
   * Given machine name input, get a unique machine name to avoid duplicate
   * errors.
   *
   * @param $input
   * @param $maxlength
   *
   * @return string
   */
  public static function generateUniqueEntityId($input, $maxlength) {
    $index = -1;

    do {
      if ($index == -1) {
        // Usually the first pass.
        $id_string = substr($input, 0, $maxlength);
      }
      else {
        $id_string = substr($input, 0, $maxlength - strlen($index) - 1) . '_' . $index;
      }

      $index += 1;
    } while (self::load($id_string));

    return $id_string;
  }

  /**
   * @inheritdoc
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel == 'add-form') {
      $uri_route_parameters['bundle'] = $this->bundle;
    }

    $uri_route_parameters['content_entity_type'] = $this->entity_type;

    return $uri_route_parameters;
  }

  /**
   * @return mixed|null
   */
  public function getMasterTemplate() {
    return $this->get('master_template');
  }

  /**
   *
   */
  public function canEditMachineName() {
    if ($this->get('bundle') === '__any__') {
      return FALSE;
    }

    if ($this->get('view_mode') !== 'full' && $this->get('modified') === FALSE) {
      return TRUE;
    }

    return parent::canEditMachineName();
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMarkup() {
    if ($this->get('bundle') == '__any__' || $this->get('view_mode') != 'full') {
      $in_use = [
        '#type' => 'markup',
        '#markup' => '-',
      ];
      return \Drupal::service('renderer')->render($in_use);
    }
    else {
      return parent::getInUseMarkup();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This Content template has been tracked as in use in the places listed below.<br/><br/>
        <b>Warning:</b> It may also be used as the default template. You should not delete this Content template unless you are sure that it’s not being used.'),
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {

    $this->clearData();
    // Only delete full view mode content template.
    if ($this->get('view_mode') == 'full') {
      $candidate_template_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_content_templates')->getQuery()
        ->accessCheck(TRUE)
        ->condition('entity_type', $this->get('entity_type'))
        ->condition('bundle', $this->get('bundle'))
        ->condition('view_mode', $this->get('view_mode'))
        ->execute();
      if (count($candidate_template_ids) > 1) {
        $this->delete();
        return;
      }
    }

    $this->setDefaultValues();
    $this->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getEntityMachineNamePrefix() {
    return 'ctn_tpl_';
  }

}
