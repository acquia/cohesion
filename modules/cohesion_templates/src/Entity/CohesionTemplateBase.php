<?php

namespace Drupal\cohesion_templates\Entity;

use Drupal\cohesion\Entity\CohesionConfigEntityBase;
use Drupal\cohesion\Entity\CohesionSettingsInterface;
use Drupal\cohesion\TemplateEntityTrait;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Class CohesionTemplateBase
 * Defines the Site Studio template base entity type.
 *
 * @package Drupal\cohesion_templates\Entity
 */
abstract class CohesionTemplateBase extends CohesionConfigEntityBase implements CohesionSettingsInterface {

  use TemplateEntityTrait;

  /**
   * @inheritdoc
   */
  public function setDefaultValues() {
    parent::setDefaultValues();

    $this->set('custom', FALSE);
    $this->set('twig_template', NULL);
  }

  /**
   * Make this the default entity of its type.
   *
   * @param bool $default
   */
  public function setDefault($default = TRUE) {
    // Set the default value.
    $this->set('default', $default);
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    $this->preProcessJsonValues();
  }

  /**
   * @inheritDoc
   */
  public function getApiPluginInstance() {
    return $this->apiProcessorManager()->createInstance('templates_api');
  }

  /**
   * {@inheritdoc}
   */
  public function process() {
    parent::process();

    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $send_to_api->send();

    // Invalidate the template cache.
    self::clearCache($this);
  }

  /**
   * {@inheritdoc}
   */
  public function jsonValuesErrors() {
    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();

    $send_to_api->setEntity($this);
    $success = $send_to_api->sendWithoutSave();
    $responseData = $send_to_api->getData();

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
  public function postSave(EntityStorageInterface $storage, $update = TRUE) {
    parent::postSave($storage);

    // Send to API only if JSON has changed;.
    if ($this->status()) {
      $this->process();
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    parent::preDelete($storage, $entities);

    foreach ($entities as $entity) {
      // Clear the cache for this component.
      self::clearCache($entity);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function clearData() {
    // Invalidate the template cache.
    self::clearCache($this);

    // Delete the entry from the stylesheet.json file.
    /** @var \Drupal\cohesion\Plugin\Api\TemplatesApi $send_to_api */
    $send_to_api = $this->getApiPluginInstance();
    $send_to_api->setEntity($this);
    $send_to_api->delete();

    // Remove all templates (global and theme specific)
    $this->removeAllTemplates();
  }

  /**
   * Delete the template twig cache (if available) and invalidate the render
   * cache tags.
   */
  protected static function clearCache($entity) {
    // The twig filename for this template.
    $filename = $entity->get('twig_template');

    _cohesion_templates_delete_twig_cache_file($filename);

    // Content template.
    if ($entity->get('entity_type') && $entity->get('bundle')) {
      $entity_cache_tags = [];

      // Template is also the default.
      if ($entity->get('default') == TRUE) {
        $entity_cache_tags[] = 'cohesion.templates.' . $entity->get('entity_type') . '.' . $entity->get('bundle') . '.' . $entity->get('view_mode') . '.__default__';
      }

      // Template is global.
      if ($entity->get('bundle') == '__any__') {
        $entity_cache_tags[] = 'cohesion.templates.' . $entity->get('entity_type') . '.' . $entity->get('view_mode');
      }
      else {
        $entity_cache_tags[] = 'cohesion.templates.' . $entity->get('entity_type') . '.' . $entity->get('bundle') . '.' . $entity->get('view_mode') . '.' . $entity->id();
      }
    }
    // All other templates.
    else {
      $entity_cache_tags = ['cohesion.templates.' . $entity->id()];
    }

    // Invalidate render cache tag for this template.
    \Drupal::service('cache_tags.invalidator')->invalidateTags($entity_cache_tags);

    // And clear the theme cache.
    parent::clearCache($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function isLayoutCanvas() {
    return TRUE;
  }

}
