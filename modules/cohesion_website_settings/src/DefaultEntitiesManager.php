<?php

namespace Drupal\cohesion_website_settings;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion_website_settings\Entity\FontStack;
use Drupal\cohesion_website_settings\Entity\WebsiteSettings;
use Drupal\Component\Serialization\Json;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Default site studio entities manager.
 *
 * @package Drupal\cohesion_website_settings
 */
class DefaultEntitiesManager {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * DefaultEntitiesManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Import/update entities from asset storage.
   *
   * Add new entities and potentially remove defunct ones.
   *
   * @param bool $purge
   */
  public function importEntities($purge = TRUE) {
    $entities = \Drupal::service('cohesion.element.storage')->getByGroup(WebsiteSettings::getAssetGroupId());

    // Import elements.
    if ($entities) {
      $canonical_list = [];

      // Import each entity.
      foreach ($entities as $e) {

        $entity_exists = WebsiteSettings::load($e['element_id']);
        // Entity type that do not need to be imported.
        $excluded_website_settings = [
          'color_palette',
          'font_libraries',
          'icon_libraries',
          'scss_variables',
        ];
        if (!$entity_exists && !in_array($e['element_id'], $excluded_website_settings)) {
          // Try to import entity.
          $entity = WebsiteSettings::create([
            'id' => $e['element_id'],
            'label' => $e['element_label'],
          ]);
          $entity->setDefaultValues();
          $entity->save();
        }
        $canonical_list[$e['element_id']] = $e['element_label'];
      }

      // Remove old entities (if desired)
      if ($purge) {
        $current_entities = WebsiteSettings::loadMultiple();
        foreach ($current_entities as $k => $v) {
          if (!array_key_exists($k, $canonical_list)) {
            $v->delete();
          }
        }
      }

      // Clear the router cache (rebuild the menu items).
      \Drupal::service('router.builder')->rebuild();
    }
  }

  /**
   * Creates default website stetings entities given an arbitrary type.
   *
   * @param $settings_type
   *
   * @return array|bool
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   */
  public function createDefaultWebsiteSettings($settings_type) {
    $ws_library = \Drupal::keyValue('cohesion.assets.website_settings');
    $form = $ws_library->get($settings_type) ?: [];

    switch ($settings_type) {
      case 'font_libraries':
        // Check to see if any font_stack entities already exist.
        if (!count($this->entityTypeManager->getStorage('cohesion_font_stack')->loadMultiple())) {
          foreach ($form['model']['fontStacks'] as $font_stack) {
            $entity = FontStack::create([
              'id' => $font_stack['stack']['uid'],
              'label' => $font_stack['stack']['name'],
            ]);
            $entity->setDefaultValues();
            $entity->setJsonValue(Json::encode($font_stack['stack']));
            $entity->save();
          }
        }

        break;

      default:
        $entity = $this->getCohesionEntity('cohesion_website_settings', $settings_type);
        if (isset($entity) && ($entity instanceof CohesionConfigEntityBase) && !$entity->isModified()) {
          // Send default icon libraries to be processed on the API.
          $entity->setJsonValue(Json::encode($form['model']));
          $entity->setJsonMapper('{}');
          $entity->enable();
          $entity->setModified();
          $errors = $entity->jsonValuesErrors();

          if (!$errors) {
            $entity->save();
            return FALSE;
          }
          else {
            return $errors;
          }
        }
        break;
    }

    return FALSE;
  }

  /**
   * Helper function.
   *
   * @param $type
   * @param $id
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|null
   */
  private function getCohesionEntity($type, $id) {
    try {
      if ($storage = $this->entityTypeManager->getStorage($type)) {
        $id = $storage->getQuery()->accessCheck(TRUE)->condition('id', $id)->execute();
        if ($id) {
          $entity = $storage->load(array_pop($id));
        }
      }
    }
    catch (\Exception $ex) {
      $entity = FALSE;
    }

    return $entity ?? FALSE;
  }

}
