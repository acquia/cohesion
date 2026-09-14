<?php

namespace Drupal\cohesion_templates\Entity;

use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Defines the master templates entity.
 *
 * @ConfigEntityType(
 *   id = "cohesion_master_templates",
 *   label = @Translation("Master template"),
 *   label_singular = @Translation("Master template"),
 *   label_plural = @Translation("Master templates"),
 *   label_collection = @Translation("Master templates"),
 *   label_count = @PluralTranslation(
 *     singular = "@count master template",
 *     plural = "@count master templates",
 *   ),
 *   handlers = {
 *     "list_builder" = "Drupal\cohesion_templates\TemplatesListBuilder",
 *     "form" = {
 *       "add" = "Drupal\cohesion_templates\Form\MasterTemplatesForm",
 *       "edit" = "Drupal\cohesion_templates\Form\MasterTemplatesForm",
 *       "duplicate" = "Drupal\cohesion_templates\Form\MasterTemplatesForm",
 *       "delete" = "Drupal\cohesion_templates\Form\TemplateDeleteForm",
 *       "enable" = "Drupal\cohesion_templates\Form\TemplateEnableForm",
 *       "disable" = "Drupal\cohesion_templates\Form\MasterTemplateDisableForm",
 *       "enable-selection" = "Drupal\cohesion\Form\CohesionEnableSelectionForm",
 *       "disable-selection" = "Drupal\cohesion\Form\CohesionDisableSelectionForm",
 *       "set_default" = "Drupal\cohesion_templates\Form\MasterTemplateSetDefaultForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\cohesion\CohesionHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "cohesion_master_templates",
 *   admin_permission = "administer master templates",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid",
 *     "status" = "status",
 *     "selectable" = "selectable"
 *   },
 *   links = {
 *     "edit-form" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/edit",
 *     "add-form" = "/admin/cohesion/templates/master_templates/add",
 *     "delete-form" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/delete",
 *     "collection" = "/admin/cohesion/templates/master_templates",
 *     "duplicate-form" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/duplicate",
 *     "enable" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/enable",
 *     "disable" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/disable",
 *     "enable-selection" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/enable-selection",
 *     "disable-selection" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/disable-selection",
 *     "set-default-form" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/set_default",
 *     "in-use" = "/admin/cohesion/templates/master_templates/{cohesion_master_templates}/in-use",
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "json_values",
 *     "json_mapper",
 *     "last_entity_update",
 *     "locked",
 *     "modified",
 *     "selectable",
 *     "custom",
 *     "twig_template",
 *     "default"
 *   }
 * )
 */
class MasterTemplates extends CohesionTemplateBase implements CohesionSettingsInterface {

  const ASSET_GROUP_ID = 'master_template';

  const ENTITY_MACHINE_NAME_PREFIX = 'mstr_tpl_';

  /**
   * Import a list of entities.
   *
   * @param $entities
   *
   * @return bool
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public static function importEntities($entities) {
    $current_entities = self::loadMultiple();
    // Only import master templates if there is some to import or if there no
    // master template already.
    if (!is_array($entities) || (count($entities) == 0) || count($current_entities) > 0) {
      return FALSE;
    }

    // Import each entity.
    $canonical_list = [];
    foreach ($entities as $e) {
      $entity_exists = self::load($e['element_id']);
      if (!$entity_exists) {
        $entity = self::create([
          'id' => $e['element_id'],
          'label' => $e['element_label'],
        ]);
        $entity->setDefaultValues();
        $entity->set('default', TRUE);
        $entity->save();
      }
      $canonical_list[$e['element_id']] = $e['element_label'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage);

    if ($this->get('default') === TRUE) {
      $default_templates_ids = \Drupal::service('entity_type.manager')->getStorage('cohesion_master_templates')->getQuery()
        ->accessCheck(TRUE)
        ->condition('default', TRUE)
        ->condition('id', $this->id(), '<>')
        ->execute();

      $default_templates = $this->loadMultiple($default_templates_ids);
      foreach ($default_templates as $default_template) {
        $default_template->set('default', FALSE);
        $default_template->save();
      }

      \Drupal::service('cohesion_usage.update_manager')->rebuildEntityType('cohesion_content_templates');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getInUseMessage() {
    return [
      'message' => [
        '#markup' => t('This master template has been tracked as in use in the places listed below. You should not delete it until you have removed its use.'),
      ],
    ];
  }

}
