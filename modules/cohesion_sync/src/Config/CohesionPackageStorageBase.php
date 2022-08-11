<?php

namespace Drupal\cohesion_sync\Config;

use Drupal\Core\Config\StorageInterface;

/**
 * Base class for Cohesion Packages using new format.
 *
 * @package Drupal\cohesion_sync\Config
 */
abstract class CohesionPackageStorageBase implements StorageInterface {

  /**
   * Files array.
   *
   * @var array
   */
  public $files = [];

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

    foreach ($this->listAll() as $name) {
      $content_dependencies = $this->getContentDependencies($name);
      foreach ($content_dependencies as $dependency) {
        if (strpos($dependency, 'file:file:') !== FALSE) {
          $file = explode(':', $dependency);
          if (isset($this->files[$file[2]]) === FALSE) {
            $this->files[$file[2]] = $file[0];
          }
        }
      }
    }
  }

  /**
   * Returns all 'config' type dependencies of config entity $id.
   *
   * @param string $id
   *   Config entity id.
   *
   * @return array
   *   Array of config entity ids.
   */
  protected function getConfigDependencies(string $id): array {
    return $this->getDependencyFromIdByTypeRecursively($id, 'config');
  }

  /**
   * Returns all 'config' type dependencies of config entity $id.
   *
   * @param string $id
   *   Config entity id.
   *
   * @return array
   *   Array of content dependencies ('file:file:uuid' for file entities).
   */
  protected function getContentDependencies(string $id) {
    return $this->getDependencyFromIdByTypeRecursively($id, 'content');
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
  protected function getDependencyFromIdByTypeRecursively(string $id, string $type) {
    $dependencies = [];

    if (!$this->exists($id)) {
      return $dependencies;
    }

    $config = $this->read($id);
    if (isset($config['dependencies'][$type])) {
      foreach ($config['dependencies'][$type] as $dependency) {
        $dependencies[] = $dependency;
        $inherited_dependencies = $this->getDependencyFromIdByTypeRecursively($dependency, $type);
        $dependencies = array_merge($dependencies, $inherited_dependencies);
      }
    }

    return array_unique($dependencies);
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
   *
   * @return array
   *   List of required config names.
   */
  protected function buildDependencies(array $site_studio_config): array {
    $dependencies = [];

    foreach ($site_studio_config as $name) {
      if ($this->exists($name)) {
        $inherited_dependencies = $this->getConfigDependencies($name);
        $dependencies = array_merge($dependencies, $inherited_dependencies);
      }
    }

    return array_unique($dependencies);
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
