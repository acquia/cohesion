<?php

namespace Drupal\cohesion\Plugin\Usage;

use Drupal\cohesion\UsagePluginBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManager;
use Drupal\file\FileRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin for file usage.
 *
 * @package Drupal\cohesion\Plugin\Usage
 *
 * @Usage(
 *   id = "file_usage",
 *   name = @Translation("File usage"),
 *   entity_type = "file",
 *   scannable = FALSE,
 *   scan_same_type = FALSE,
 *   group_key = FALSE,
 *   group_key_entity_type = FALSE,
 *   exclude_from_package_requirements = TRUE,
 *   exportable = FALSE,
 *   config_type = "core",
 *   scan_groups = {"core", "site_studio"},
 *   can_be_excluded = FALSE
 * )
 */
class FileUsage extends UsagePluginBase {

  /**
   * Use a regex to scan the JSON blog for usages of file URIs.
   *
   * @var string
   */
  protected $uriRegex;

  /**
   * @var string
   */
  protected $mediaReferenceRegex;

  /**
   * All available stream wrappers on the site.
   *
   * @var array|mixed
   */
  protected $wrappers;

  /**
   * Drupal File Repository service.
   *
   * @var \Drupal\file\FileRepositoryInterface
   */
  protected $fileRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new self(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager'),
      $container->get('stream_wrapper_manager'),
      $container->get('database'),
      $container->get('file.repository'),
    );
  }

  /**
   * FileUsage constructor.
   *
   * @param $configuration
   * @param $plugin_id
   * @param $plugin_definition
   * @param $entity_type_manager
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManager $stream_wrapper_manager
   * @param \Drupal\Core\Database\Connection $connection
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(
    $configuration,
    $plugin_id,
    $plugin_definition,
    $entity_type_manager,
    StreamWrapperManager $stream_wrapper_manager,
    Connection $connection,
    FileRepositoryInterface $fileRepository,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $entity_type_manager, $stream_wrapper_manager, $connection);

    // Create the URI regex based off the available stream wrappers.
    $this->wrappers = $stream_wrapper_manager->getWrappers(StreamWrapperInterface::LOCAL);

    if (empty($this->wrappers)) {
      $this->wrappers = ['cohesion' => [], 'public' => []];
    }

    foreach ($this->wrappers as $key => $wrapper) {
      $this->wrappers[$key]['instance'] = $stream_wrapper_manager->getViaUri($key . '://');
    }

    // This regex now handles spaces in filenames.
    $this->uriRegex = '/(?<!"preview_image":{"id":")((' . implode('|', array_keys($this->wrappers)) . '):\/\/(.*?)\.(.*?))([\s|:"*?<>|\\\\]|$)/m';
    $this->mediaReferenceRegex = '/\[media-reference:file:(.*?)\]/m';
    $this->fileRepository = $fileRepository;
  }

  /**
   * {@inheritdoc}jso.
   */
  public function getScannableData(EntityInterface $entity) {
    return FALSE;
  }

  /**
   * @param $files
   * @param $uri
   *
   * @return \Drupal\file\FileInterface|false|mixed
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function setPermFiles($files, $uri) {
    if (!empty($files)) {
      return reset($files);
    }
    else {
      if (empty($files) && file_exists($uri)) {
        // Make file selected within Site Studio managed and permanent.
        $contents = file_get_contents($uri);
        $file = $this->fileRepository->writeData($contents, $uri, FileSystemInterface::EXISTS_REPLACE);
        $file->setPermanent();
        $file->save();
        return $file;
      }
    }
  }

  /**
   * Turn a managed file into a managed file (if it's not already one).
   *
   * @param $uri
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|\Drupal\file\FileInterface|false|mixed
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function getFileEntityByUri($uri) {
    $files = $this->storage->loadByProperties(['uri' => $uri]);
    return $this->setPermFiles($files, $uri);
  }

  /**
   * @param $uuid
   *
   * @return bool
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  private function getFileEntityByUuid($uuid) {
    $files = $this->storage->loadByProperties(['uuid' => $uuid]);

    if (!empty($files)) {
      $file = reset($files);
      return $this->setPermFiles($files, $file->getFileUri());
    }
    else {
      return FALSE;
    }
  }

  /**
   * Register file usage with core 'file.usage' service.
   *
   * @param $files
   * @param \Drupal\Core\Entity\EntityInterface $entity
   */
  private function registerCoreFileUsage($files, EntityInterface $entity) {

    if ($entity->id() && strlen($entity->id()) < 32) {
      // Remove 'cohesion' registered usage of all files for scanned entity.
      try {
        $this->connection->delete('file_usage')
          ->condition('module', 'cohesion')
          ->condition('type', $entity->getEntityTypeId())
          ->condition('id', $entity->id())
          ->execute();
      }
      catch (\Throwable $e) {
        return;
      }

      // Add file usages back in one by one, so we end up with the correct count
      // for duplicates.
      foreach ($files as $file) {
        \Drupal::service('file.usage')
          ->add($file, 'cohesion', $entity->getEntityTypeId(), $entity->id(), 1);
        $this->refreshFileListCache($file);
      }
    }
  }

  /**
   * Invalidate the file cache tags so the file usage view rebuilds.
   *
   * @param \Drupal\Core\Entity\EntityInterface $file_entity
   */
  private function refreshFileListCache(EntityInterface $file_entity) {
    $tags = $file_entity->getEntityType()->getListCacheTags();
    if ($file_entity->hasLinkTemplate('canonical')) {

      // Creating or updating an entity may change a cached 403 or 404 response.
      $tags = Cache::mergeTags($tags, [
        '4xx-response',
      ]);
    }

    // An existing entity was updated, also invalidate its unique cache tag.
    $tags = Cache::mergeTags($tags, $file_entity->getCacheTagsToInvalidate());
    Cache::invalidateTags($tags);
  }

  /**
   * Patch all known stream wrapper basepaths into the content to catch files
   * that are in use but not defined as URIs like stream://.
   *
   * @param $data
   */
  private function convertBasepathsToUris(&$data) {
    foreach ($this->wrappers as $key => $wrapper) {
      if (method_exists($wrapper['instance'], 'getDirectoryPath')) {
        if ($base_path = $wrapper['instance']->getDirectoryPath()) {
          $data = str_replace('/' . $base_path . '/', $key . '://', $data);
        }
      }
    }

  }

  /**
   * {@inheritdoc}
   */
  public function scanForInstancesOfThisType($data, EntityInterface $entity) {
    $entities = parent::scanForInstancesOfThisType($data, $entity);
    $uris = [];
    $uuids = [];

    foreach ($data as $entry) {
      if ($entry['type'] == 'json_string' || $entry['type'] == 'string') {
        // Get all the files used by URI.
        // Cheaply patch the JSON.
        $entry['value'] = str_replace('\\/', '/', $entry['value']);
        $this->convertBasepathsToUris($entry['value']);

        preg_match_all($this->uriRegex, $entry['value'], $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
          // Found a matching file.
          if (!strstr($match[1], 'styles/dx8_component_preview/')) {
            $uris[] = $match[1];
          }
        }

        // Get all the files used by media-reference token.
        preg_match_all($this->mediaReferenceRegex, $entry['value'], $matches, PREG_SET_ORDER, 0);

        foreach ($matches as $match) {
          // Found a matching file.
          $uuids[] = $match[1];
        }

      }
    }

    // Add files by found URIs.
    $files = [];

    if (!empty($uris)) {
      foreach ($uris as $uri) {
        // Load the file, if it doesn't exist, create it.
        if ($file = $this->getFileEntityByUri($uri)) {

          // Tell core 'file.usage' that this file is in use by this entity.
          $files[] = $file;

          // Save the Site Studio in-use.
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $file->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    // Add files by found UUIDs.
    if (!empty($uuids)) {
      foreach ($uuids as $uuid) {
        if ($file = $this->getFileEntityByUuid($uuid)) {
          // Tell core 'file.usage' that this file is in use by this entity.
          $files[] = $file;

          // Save the Site Studio in-use.
          $entities[] = [
            'type' => $this->getEntityType(),
            'uuid' => $file->uuid(),
            'subid' => NULL,
          ];
        }
      }
    }

    // Register file usages with core 'file.usage'.
    if (!empty($files)) {
      $this->registerCoreFileUsage($files, $entity);
    }

    return $entities;
  }

  /**
   * Handles `file_usage` table entries and cache.
   */
  public function removeUsage(EntityInterface $file_entity, EntityInterface $entity) {
    // Invalidate the file cache tags so the file usage view rebuilds.
    $this->refreshFileListCache($file_entity);

    // Update the file entity changed timestamp so it gets cleared on cron.
    /** @var \Drupal\file\Entity\File $file_entity */
    $file_entity->setChangedTime(\Drupal::time()->getRequestTime());

    // Remove CORE 'cohesion' registered usage of all files for scanned entity.
    $this->connection->delete('file_usage')->condition('module', 'cohesion')->condition('type', $entity->getEntityTypeId())->condition('id', $entity->id())->execute();
  }

}
