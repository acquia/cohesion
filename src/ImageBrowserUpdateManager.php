<?php

namespace Drupal\cohesion;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepository;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\file\FileInterface;
use Drupal\media\MediaInterface;

/**
 * Defines the image browser update manager.
 *
 * @package Drupal\cohesion
 */
class ImageBrowserUpdateManager {

  /**
   * Holds the ImageBrowser plugin manager service.
   *
   * @var \Drupal\cohesion\ImageBrowserPluginManager
   */
  protected $pluginManager;

  /**
   * Holds the module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * @var \Drupal\cohesion\ImageBrowserPluginInterface
   */
  protected $pluginInstanceConfig;

  /**
   * @var \Drupal\cohesion\ImageBrowserPluginInterface
   */
  protected $pluginInstanceContent;

  /**
   * @var \Drupal\Core\Entity\EntityRepository
   */
  protected $entityRepository;

  /**
   * @var array|mixed|null
   */
  protected $image_browser_config;

  /**
   * ImageBrowserUpdateManager constructor.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   * @param \Drupal\cohesion\ImageBrowserPluginManager $pluginManager
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   * @param \Drupal\Core\Entity\EntityRepository $entityRepository
   */
  public function __construct(
    ConfigFactoryInterface $configFactory,
    ImageBrowserPluginManager $pluginManager,
    ModuleHandlerInterface $moduleHandler,
    EntityRepository $entityRepository,
  ) {
    $this->pluginManager = $pluginManager;
    $this->moduleHandler = $moduleHandler;
    $this->entityRepository = $entityRepository;

    $this->image_browser_config = $configFactory->get('cohesion.settings')->get('image_browser');

    if (isset($this->image_browser_config['config']['type'])) {
      // Get an instance of the active image browser plugin.
      try {
        $this->pluginInstanceConfig = $this->pluginManager->createInstance($this->image_browser_config['config']['type']);

        // If the module is disabled, don't load the plugin.
        if (!$this->moduleHandler->moduleExists($this->pluginInstanceConfig->getModule())) {
          $this->pluginInstanceConfig = NULL;
        }
      } catch (\Exception $e) {
        $this->pluginInstanceConfig = NULL;
      }
    }

    if (isset($this->image_browser_config['content']['type'])) {
      // Get an instance of the active image browser plugin.
      try {
        $this->pluginInstanceContent = $this->pluginManager->createInstance($this->image_browser_config['content']['type']);

        // If the module is disabled, don't load the plugin.
        if (!$this->moduleHandler->moduleExists($this->pluginInstanceContent->getModule())) {
          $this->pluginInstanceContent = NULL;
        }
      } catch (\Exception $e) {
        $this->pluginInstanceContent = NULL;
      }
    }

  }

  /**
   * Run sharedPageAttachments on both plugins.
   *
   * @param $attachments
   * @param $type
   */
  public function sharedPageAttachments(&$attachments, $type) {
    if ($type == 'config' && $this->pluginInstanceConfig) {
      $this->pluginInstanceConfig->sharedPageAttachments('config', $attachments);
    }

    if ($type == 'content' && $this->pluginInstanceContent) {
      $this->pluginInstanceContent->sharedPageAttachments('content', $attachments);
    }
  }

  /**
   * Run onEntityInsertUpdate on both plugins.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  public function onEntityInsertUpdate(EntityInterface $entity) {
    if ($this->pluginInstanceConfig) {
      $this->pluginInstanceConfig->onEntityInsertUpdate($entity);
    }

    if ($this->pluginInstanceContent) {
      $this->pluginInstanceContent->onEntityInsertUpdate($entity);
    }
  }

  /**
   * Loop through all plugins and if their dependent module is enabled, add to
   * the list.
   *
   * @return array
   */
  public function getAvailablePlugins() {
    $plugins = [];

    foreach ($this->pluginManager->getDefinitions() as $id => $definition) {
      try {
        $instance = $this->pluginManager->createInstance($id);

        if ($this->moduleHandler->moduleExists($instance->getModule())) {
          $plugins[$id] = $instance;
        }
      }
      catch (\Exception $e) {
        continue;
      }
    }

    return $plugins;
  }

  /**
   * Decodes a [media-reference:?:?] token and return the Uri.
   *
   * @param $token
   *
   * @return array|bool
   */
  public function decodeToken($token) {
    $token = explode(':', str_replace(['[', ']'], '', $token));

    if (is_array($token) && count($token)) {
      // Load the entity by UUID in the token.
      try {
        $entity = $this->entityRepository->loadEntityByUuid($token[1], $token[2]);
      } catch (\Exception $e) {
        return FALSE;
      }

      if ($entity) {
        // Switch the entity types.
        switch ($token[1]) {
          // This is a file entity reference (usually IMCE).
          case 'file':
            if ($entity instanceof FileInterface) {
              return [
                'path' => $entity->getFileUri(),
                'entity' => $entity,
                'label' => $entity->label(),
              ];
            }
            break;

          // This is a media entity reference (Entity Browser).
          case 'media':
            // Checking for Acquia DAM assets and using original embeds.
            if ($entity instanceof MediaInterface && strpos($entity->getSource()->getPluginId(), 'acquia_dam_asset') === 0) {
              ['asset_id' => $asset_id, 'version_id' => $version_id] = $entity->getSource()->getSourceFieldValue($entity);
              $uri = "acquia-dam://$asset_id/$version_id";

              return [
                'path' => $uri,
                'entity' => $entity,
                'label' => $entity->label(),
              ];
            }

            if ($image_uri = $entity->getSource()->getMetadata($entity, 'thumbnail_uri')) {
              return [
                'path' => $image_uri,
                'entity' => $entity,
                'label' => $entity->label(),
              ];
            }
            break;

          // If not a file or media, its another entity type from an
          // Entity browser so just display the title.
          default:
            return [
              'path' => '',
              'entity' => '',
              'label' => $entity->label(),
            ];
        }
      }
    }

    return FALSE;
  }

  /**
   * Returns the path & label to be used for thumbnails on entity browser
   * & image form fields.
   *
   * @param $reference
   * @return array
   */
  public function getPreviewThumbnail($reference) {
    // Decode the media reference token.
    $decoded_token = $this->decodeToken($reference);
    $entity = $decoded_token['entity'];

    // Make sure there is a path & entity.
    if ($decoded_token['path'] && $entity) {
      $entity_type = $entity->getEntityTypeId();

      if ($entity_type == 'file') {
        return [
          'path' => $entity->getFileUri(),
          'label' => $entity->label(),
        ];
      }
      elseif ($entity_type == 'media') {
        return [
          'path' => $entity->getSource()->getMetadata($entity, 'thumbnail_uri'),
          'label' => $entity->label(),
        ];
      }
    }

    return [
      'path' => '',
      'label' => $decoded_token['label'],
    ];
  }

}
