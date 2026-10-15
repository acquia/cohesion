<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\Core\Config\StorageInterface;

/**
 * Base class for Cohesion Packages using new format.
 *
 * @package Drupal\cohesion_sync\Config
 */
abstract class CohesionPackageStorageBase implements StorageInterface {

  const DEPENDENCY_TYPES = ['config', 'content'];

  /**
   * Storage identifier.
   *
   * @var string
   */
  protected $storage;

  /**
   * Files array.
   *
   * @var array
   */
  public $files = [];

  /**
   * Dependencies array.
   *
   * @var array
   */
  public $dependencies = [];

  /**
   * Gets array of file uuids.
   *
   * @return array
   *   Array of files Site Studio config has dependencies on.
   * @throws \Exception
   */
  public function getStorageFileList() {
    if (empty($this->files)) {
      $this->buildStorageFileList();
    }

    return $this->files;
  }

  /**
   * Builds a list of files that Site Studio config and its dependencies are
   * dependening on.
   */
  protected function buildStorageFileList() {
    $this->files = [];

    $content_dependencies = $this->getContentDependencies($this->listAll());
    foreach ($content_dependencies as $dependency) {
      if (strpos($dependency, 'file:file:') !== FALSE) {
        $file = explode(':', $dependency);
        if (isset($this->files[$file[2]]) === FALSE) {
          $this->files[$file[2]] = $file[0];
        }
      }
    }
  }

  /**
   * Returns all 'config' type dependencies of $config array.
   *
   * @param array $config
   *   Config entities ids
   *
   * @return array
   *   Array of config dependencies.
   */
  protected function getConfigDependencies(array $config): array {
    if (empty($this->dependencies)) {
      $this->buildDependencies($config);
    }

    return $this->dependencies['config'] ?? [];
  }

  /**
   * Returns all 'content' type dependencies of $config array.
   *
   * @param array $config
   *   Config entities ids.
   *
   * @return array
   *   Array of content dependencies.
   */
  protected function getContentDependencies(array $config) {
    if (empty($this->dependencies)) {
      $this->buildDependencies($config);
    }

    return $this->dependencies['content'] ?? [];
  }

  /**
   * Traverses dependency tree of $name and builds list of $type dependencies.
   *
   * @param string $id
   *   Id of config entity to traverse.
   * @param string $type
   *   Type of dependency to look for - 'config', 'content' or 'module'.
   *
   * @return array
   */
  protected function buildDependenciesRecursively(string $id) {

    if (!$this->exists($id)) {
      return [];
    }

    $config = $this->read($id);
    foreach (self::DEPENDENCY_TYPES as $type) {
      if (isset($config['dependencies'][$type])) {
        foreach ($config['dependencies'][$type] as $dependency) {
          if (in_array($dependency, $this->dependencies[$type])) {
            continue;
          }
          $this->dependencies[$type][] = $dependency;
          $this->buildDependenciesRecursively($dependency);
        }
      }
      if (!empty($this->dependencies[$type])) {
        $this->dependencies[$type] = array_unique($this->dependencies[$type]);
      }
    }

    return $this->dependencies;
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = '') {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection) {
    return $this->storage->createCollection($collection);
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames() {
    return $this->storage->getAllCollectionNames();
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName(): string {
    return $this->storage->getCollectionName();
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names): array {
    $list = [];
    foreach ($names as $name) {
      if ($data = $this->read($name)) {
        $list[$name] = $data;
      }
    }
    return $list;
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name): bool {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data): string {
    return $this->storage->encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw): array {
    return $this->storage->decode($raw);
  }

  /**
   * Builds a list of config names based on Site Studio config dependencies.
   *
   * @param array $site_studio_config
   *   List of site studio config entity names.
   */
  protected function buildDependencies(array $site_studio_config) {
    $this->dependencies = [
      'config' => [],
      'content' => [],
    ];

    foreach ($site_studio_config as $name) {
      if ($this->exists($name)) {
        $this->buildDependenciesRecursively($name);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function exists($name): bool {
    return $this->storage->exists($name);
  }

  /**
   * Returns the status of config, if config has status.
   *
   * @param string $name
   *   Config name.
   *
   * @return bool
   *   True if status property exists and is set to True.
   */
  protected function configStatus(string $name): bool {
    $config = $this->read($name);

    return (!isset($config['status']) || $config['status'] == TRUE);
  }

}
